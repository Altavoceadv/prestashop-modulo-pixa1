<?php
/**
 * Delivery Date Calculator Class - classes/DeliveryDateCalculator.php
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

class DeliveryDateCalculator
{
    private $working_days;
    private $holidays;
    private $cutoff_time;

    public function __construct()
    {
        // Get configuration from PrestaShop
        $this->working_days = explode(',', Configuration::get('CUSTOM_PRODUCT_WORKING_DAYS') ?: '1,2,3,4,5');
        $this->holidays = array_filter(explode("\n", Configuration::get('CUSTOM_PRODUCT_HOLIDAYS') ?: ''));
        $this->cutoff_time = Configuration::get('CUSTOM_PRODUCT_CUTOFF_TIME') ?: '16:00';
    }

    /**
     * Calculate delivery date based on working days
     */
    public function calculateDeliveryDate($delivery_days, $base_date = null)
    {
        if (!$base_date) {
            $base_date = new DateTime();
        }

        // Check if we're past cutoff time
        if ($this->isPastCutoffTime($base_date)) {
            $base_date->add(new DateInterval('P1D'));
        }

        $delivery_date = clone $base_date;
        $added_days = 0;

        while ($added_days < $delivery_days) {
            $delivery_date->add(new DateInterval('P1D'));
            
            if ($this->isWorkingDay($delivery_date)) {
                $added_days++;
            }
        }

        return $delivery_date;
    }

    /**
     * Check if current time is past cutoff
     */
    private function isPastCutoffTime($date)
    {
        $current_time = $date->format('H:i');
        return $current_time > $this->cutoff_time;
    }

    /**
     * Check if date is a working day
     */
    private function isWorkingDay($date)
    {
        $day_of_week = (int)$date->format('N'); // 1=Monday, 7=Sunday
        
        // Check if it's a working day
        if (!in_array($day_of_week, array_map('intval', $this->working_days))) {
            return false;
        }
        
        // Check if it's a holiday
        $date_string = $date->format('Y-m-d');
        if (in_array($date_string, $this->holidays)) {
            return false;
        }
        
        return true;
    }

    /**
     * Get delivery columns for frontend
     */
    public function getDeliveryColumns($delivery_days_array, $base_date = null)
    {
        $columns = [];
        $today = $base_date ?: new DateTime();
        
        foreach ($delivery_days_array as $days) {
            $delivery_date = $this->calculateDeliveryDate($days, $today);
            
            $columns[] = [
                'days' => (int)$days,
                'date' => $delivery_date,
                'day_name' => $this->getItalianDayName($delivery_date),
                'formatted_date' => $delivery_date->format('d/m'),
                'full_date' => $delivery_date->format('Y-m-d'),
                'timestamp' => $delivery_date->getTimestamp()
            ];
        }
        
        return $columns;
    }

    /**
     * Get Italian day name
     */
    private function getItalianDayName($date)
    {
        $days = [
            1 => 'lunedì',
            2 => 'martedì', 
            3 => 'mercoledì',
            4 => 'giovedì',
            5 => 'venerdì',
            6 => 'sabato',
            7 => 'domenica'
        ];
        
        return $days[(int)$date->format('N')];
    }

    /**
     * Get Italian month name
     */
    private function getItalianMonthName($date)
    {
        $months = [
            1 => 'gennaio', 2 => 'febbraio', 3 => 'marzo', 4 => 'aprile',
            5 => 'maggio', 6 => 'giugno', 7 => 'luglio', 8 => 'agosto',
            9 => 'settembre', 10 => 'ottobre', 11 => 'novembre', 12 => 'dicembre'
        ];
        
        return $months[(int)$date->format('n')];
    }

    /**
     * Get formatted delivery date string
     */
    public function getFormattedDeliveryDate($delivery_days, $base_date = null)
    {
        $delivery_date = $this->calculateDeliveryDate($delivery_days, $base_date);
        $day_name = $this->getItalianDayName($delivery_date);
        $day = $delivery_date->format('j');
        $month = $this->getItalianMonthName($delivery_date);
        
        return ucfirst($day_name) . ' ' . $day . ' ' . $month;
    }

    /**
     * Check if delivery is possible for given days
     */
    public function isDeliveryPossible($delivery_days, $base_date = null)
    {
        try {
            $delivery_date = $this->calculateDeliveryDate($delivery_days, $base_date);
            return $delivery_date instanceof DateTime;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Get next working day
     */
    public function getNextWorkingDay($base_date = null)
    {
        $date = $base_date ? clone $base_date : new DateTime();
        
        do {
            $date->add(new DateInterval('P1D'));
        } while (!$this->isWorkingDay($date));
        
        return $date;
    }

    /**
     * Calculate working days between two dates
     */
    public function getWorkingDaysBetween($start_date, $end_date)
    {
        $current = clone $start_date;
        $working_days = 0;
        
        while ($current <= $end_date) {
            if ($this->isWorkingDay($current)) {
                $working_days++;
            }
            $current->add(new DateInterval('P1D'));
        }
        
        return $working_days;
    }

    /**
     * Add holidays from array
     */
    public function addHolidays($holidays)
    {
        $this->holidays = array_merge($this->holidays, $holidays);
        $this->holidays = array_unique($this->holidays);
    }

    /**
     * Remove holiday
     */
    public function removeHoliday($holiday)
    {
        $key = array_search($holiday, $this->holidays);
        if ($key !== false) {
            unset($this->holidays[$key]);
            $this->holidays = array_values($this->holidays);
        }
    }

    /**
     * Set working days
     */
    public function setWorkingDays($working_days)
    {
        $this->working_days = $working_days;
    }

    /**
     * Set cutoff time
     */
    public function setCutoffTime($cutoff_time)
    {
        $this->cutoff_time = $cutoff_time;
    }

    /**
     * Get delivery date with urgency levels
     */
    public function getDeliveryWithUrgency($delivery_days, $base_date = null)
    {
        $delivery_date = $this->calculateDeliveryDate($delivery_days, $base_date);
        $urgency = '';
        
        if ($delivery_days <= 3) {
            $urgency = 'urgent';
        } elseif ($delivery_days <= 5) {
            $urgency = 'standard';
        } else {
            $urgency = 'relaxed';
        }
        
        return [
            'date' => $delivery_date,
            'urgency' => $urgency,
            'formatted' => $this->getFormattedDeliveryDate($delivery_days, $base_date)
        ];
    }

    /**
     * Get all Italian holidays for a year
     */
    public static function getItalianHolidays($year)
    {
        $holidays = [];
        
        // Fixed holidays
        $holidays[] = $year . '-01-01'; // New Year
        $holidays[] = $year . '-01-06'; // Epiphany
        $holidays[] = $year . '-04-25'; // Liberation Day
        $holidays[] = $year . '-05-01'; // Labour Day
        $holidays[] = $year . '-06-02'; // Republic Day
        $holidays[] = $year . '-08-15'; // Assumption
        $holidays[] = $year . '-11-01'; // All Saints
        $holidays[] = $year . '-12-08'; // Immaculate Conception
        $holidays[] = $year . '-12-25'; // Christmas
        $holidays[] = $year . '-12-26'; // Boxing Day
        
        // Easter-based holidays (variable dates)
        $easter = easter_date($year);
        $easter_monday = date('Y-m-d', $easter + 86400);
        $holidays[] = $easter_monday;
        
        return $holidays;
    }

    /**
     * Auto-populate holidays for current and next year
     */
    public function autoPopulateHolidays()
    {
        $current_year = (int)date('Y');
        $next_year = $current_year + 1;
        
        $holidays = array_merge(
            self::getItalianHolidays($current_year),
            self::getItalianHolidays($next_year)
        );
        
        $this->holidays = array_unique(array_merge($this->holidays, $holidays));
        
        // Update configuration
        Configuration::updateValue('CUSTOM_PRODUCT_HOLIDAYS', implode("\n", $this->holidays));
    }
}