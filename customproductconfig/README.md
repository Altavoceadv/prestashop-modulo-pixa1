# 🎄 Custom Product Configurator for PrestaShop 8

Un modulo avanzato per PrestaShop 8 che permette di creare configuratori di prodotto personalizzati con matrice prezzi dinamica e calcolo automatico delle date di consegna. Perfetto per scatole di panettoni di Natale, prodotti personalizzabili e articoli con multiple opzioni.

[![PrestaShop 8](https://img.shields.io/badge/PrestaShop-8.0+-blue.svg)](https://www.prestashop.com/)
[![PHP](https://img.shields.io/badge/PHP-7.4+-purple.svg)](https://php.net/)
[![License](https://img.shields.io/badge/License-MIT-green.svg)](LICENSE)
[![Version](https://img.shields.io/badge/Version-1.0.0-orange.svg)](https://github.com/yourcompany/customproductconfig)

## 🚀 Caratteristiche Principali

### ✨ **Interface Utente Avanzata**
- 🎨 **Design Responsive** ottimizzato per mobile e desktop
- 🖱️ **Configuratore Visuale** con opzioni cliccabili e feedback immediato
- 📱 **Touch-Friendly** con swipe navigation per mobile
- ♿ **Accessibile** con supporto screen reader e navigazione da tastiera

### 💰 **Sistema Prezzi Dinamico**
- 📊 **Griglia Prezzi** interattiva in stile PixartPrinting
- 🔄 **Calcolo Real-time** via AJAX con caching intelligente
- 💱 **Gestione IVA** con toggle senza/con IVA
- 🏷️ **Sconti Automatici** basati su quantità e tempistiche

### 📅 **Calcolo Date Consegna**
- 🗓️ **Giorni Lavorativi** configurabili
- 🎯 **Orario Limite** per ordini giornalieri
- 🌴 **Gestione Festività** italiana automatica
- 📍 **Date Precise** con nomi giorni localizzati

### 🎛️ **Attributi Personalizzabili**
- 📦 **Formato Scatola** con moltiplicatori prezzo
- 📄 **Tipo di Carta** (Standard, Premium, Luxury)
- ✨ **Finitura** (Opaca, Lucida, Soft Touch)
- 🎨 **Colori Stampa** (1 Colore, 2 Colori, Quadricromia)

### 🔧 **Admin Panel Completo**
- ⚙️ **Configurazione Prodotti** drag-and-drop
- 📈 **Matrice Prezzi** con generazione automatica
- 📊 **Import/Export CSV** per gestione massa
- 🔄 **Copia Configurazioni** tra prodotti
- 🧪 **Test Calculator** per verifiche rapide

## 📦 Installazione

### Requisiti di Sistema

- **PrestaShop**: 8.0.0 o superiore
- **PHP**: 7.4 - 8.3
- **MySQL**: 5.7 o superiore
- **Estensioni PHP**: `json`, `curl`, `mbstring`, `intl`
- **Memoria PHP**: minimo 128MB raccomandato

### Metodo 1: Upload ZIP (Raccomandato)

1. **Scarica** il file `customproductconfig.zip`
2. **Accedi** al tuo admin PrestaShop
3. **Vai** a `Moduli` → `Module Manager`
4. **Clicca** su "Carica un modulo"
5. **Seleziona** il file ZIP e carica
6. **Installa** il modulo quando appare nell'elenco

### Metodo 2: Upload FTP

1. **Estrai** il contenuto del ZIP
2. **Carica** la cartella `customproductconfig/` in `/modules/`
3. **Vai** a `Moduli` → `Module Manager`
4. **Cerca** "Custom Product Configurator"
5. **Installa** il modulo

### Metodo 3: CLI (Avanzato)

```bash
# Upload della cartella
scp -r customproductconfig/ user@server:/path/to/prestashop/modules/

# Installazione via CLI
php bin/console prestashop:module:install customproductconfig
```

## ⚙️ Configurazione Iniziale

### 1. Configurazione Globale

**Percorso**: `Moduli` → `Custom Product Configurator` → `Configura`

```
Giorni di Consegna: 3,5,7,10,14
Festività: 2024-12-25
          2024-12-26
          2025-01-01
Orario Limite: 16:00
Giorni Lavorativi: 1,2,3,4,5
```

### 2. Configurazione Prodotto

**Percorso**: `Catalogo` → `Prodotti` → `Modifica Prodotto` → Tab `Custom Product Configurator`

1. **Abilita** configurazione personalizzata
2. **Imposta** quantità minima (es. 50)
3. **Definisci** prezzo base (es. 100€)
4. **Configura** attributi per categoria
5. **Genera** matrice prezzi automatica

### 3. Attributi Standard

Il modulo include attributi pre-configurati per panettoni:

#### 📦 **Formato Scatola**
| Opzione | Moltiplicatore |
|---------|----------------|
| Piccolo (15x15cm) | 1.0x |
| Medio (20x20cm) | 1.2x |
| Grande (25x25cm) | 1.5x |

#### 📄 **Tipo di Carta** 
| Opzione | Moltiplicatore |
|---------|----------------|
| Standard 300gr | 1.0x |
| Premium 400gr | 1.3x |
| Luxury 500gr | 1.6x |

#### ✨ **Finitura**
| Opzione | Moltiplicatore |
|---------|----------------|
| Opaca | 1.0x |
| Lucida | 1.1x |
| Soft Touch | 1.25x |

#### 🎨 **Colori di Stampa**
| Opzione | Moltiplicatore |
|---------|----------------|
| 1 Colore | 1.0x |
| 2 Colori | 1.4x |
| Quadricromia | 2.0x |

## 💡 Utilizzo

### Frontend Cliente

1. **Naviga** alla pagina prodotto configurabile
2. **Seleziona** caratteristiche (formato, carta, finitura, colori)
3. **Scegli** quantità e data consegna dalla griglia
4. **Visualizza** riepilogo con prezzo finale
5. **Aggiungi** al carrello

### Backend Amministratore

#### Configurazione Rapida
```
1. Prodotti → Seleziona prodotto
2. Tab "Custom Product Configurator"
3. Abilita configurazione → SÌ
4. Genera matrice automatica
5. Salva configurazione
```

#### Gestione Avanzata
- 🔄 **Copia configurazioni** tra prodotti simili
- 📊 **Import CSV** per aggiornamenti massa
- 🧪 **Test calculator** per verifiche
- 📈 **Statistiche** configurazioni più popolari

## 📊 Struttura Database

Il modulo crea 5 tabelle ottimizzate:

### `ps_custom_product_config`
Configurazione principale prodotti
```sql
id_config, id_product, active, minimum_order_qty, 
base_price, date_add, date_upd
```

### `ps_custom_product_attribute`
Attributi personalizzati (formato, carta, finitura, colori)
```sql
id_attribute, id_product, attribute_type, attribute_name, 
price_multiplier, position, active
```

### `ps_custom_product_pricing`
Matrice prezzi (quantità × giorni consegna)
```sql
id_pricing, id_product, quantity, delivery_days, 
price, discount_percentage, active
```

### `ps_custom_cart_product`
Personalizzazioni nel carrello
```sql
id_cart_custom, id_cart, id_product, customization_data, 
final_price, date_add
```

### `ps_custom_order_product`
Personalizzazioni negli ordini completati
```sql
id_order_custom, id_order, id_product, customization_data, 
final_price, delivery_date
```

## 🛠️ Personalizzazione

### Aggiungere Nuovi Attributi

```php
// Metodo 1: Via Admin Panel
1. Vai a Configurazione Prodotto
2. Espandi sezione attributi
3. Aggiungi nuovo attributo
4. Imposta moltiplicatore prezzo

// Metodo 2: Via Codice
$attribute = new CustomProductAttribute();
$attribute->id_product = 123;
$attribute->attribute_type = 'materiale';
$attribute->attribute_name = 'Legno Pregiato';
$attribute->price_multiplier = 2.5;
$attribute->save();
```

### Personalizzare CSS

```css
/* Override colori principale */
:root {
    --primary-color: #your-brand-color;
    --secondary-color: #your-secondary-color;
}

/* Personalizzare griglia prezzi */
.pricing-grid {
    border-radius: 15px;
    box-shadow: 0 4px 25px rgba(0,0,0,0.1);
}
```

### Hook Personalizzati

Il modulo espone diversi hook per integrazioni:

```php
// Hook dopo selezione attributo
Hook::exec('actionCustomProductAttributeSelected', [
    'id_product' => $id_product,
    'attribute_type' => $type,
    'attribute_value' => $value,
    'price_multiplier' => $multiplier
]);

// Hook dopo calcolo prezzo
Hook::exec('actionCustomProductPriceCalculated', [
    'id_product' => $id_product,
    'base_price' => $base_price,
    'final_price' => $final_price,
    'configuration' => $config
]);
```

## 🔌 API & Integrazioni

### Endpoint AJAX Frontend

**Base URL**: `/modules/customproductconfig/controllers/front/ajax.php`

#### Calcolare Prezzo
```javascript
POST /ajax
{
    action: 'calculatePrice',
    id_product: 123,
    quantity: 500,
    delivery_days: 5,
    attributes: {
        formato: 'Medio (20x20cm)',
        carta: 'Premium 400gr',
        finitura: 'Lucida',
        colori: 'Quadricromia'
    },
    with_tax: true
}

// Response
{
    success: true,
    price: 850.50,
    unit_price: 1.701,
    currency: '€'
}
```

#### Aggiungere al Carrello
```javascript
POST /ajax
{
    action: 'addToCart',
    id_product: 123,
    quantity: 500,
    delivery_days: 5,
    attributes: { ... }
}

// Response
{
    success: true,
    cart_id: 456,
    message: 'Prodotto aggiunto al carrello!'
}
```

### API Admin

**Base URL**: `/admin-xyz/index.php?controller=AdminCustomProduct`

#### Generare Matrice Prezzi
```javascript
POST /admin
{
    action: 'generateMatrix',
    id_product: 123,
    base_price: 100.00
}
```

#### Import/Export CSV
```javascript
// Export
GET /admin?action=exportCSV&id_product=123

// Import
POST /admin
FormData: {
    action: 'importCSV',
    id_product: 123,
    csv_file: [File]
}
```

## 📱 Mobile & Responsive

### Caratteristiche Mobile
- 🖱️ **Touch Navigation** con swipe per scorrere prezzi
- 📱 **Layout Adattivo** che si adatta a tutte le dimensioni
- 👆 **Tap Targets** ottimizzati per touch (44px minimo)
- 📊 **Griglia Scorrevole** con indicatori di posizione
- ⚡ **Caricamento Veloce** con lazy loading immagini

### Breakpoint CSS
```css
/* Mobile First */
@media (max-width: 480px) { /* Smartphone */ }
@media (max-width: 768px) { /* Tablet */ }
@media (max-width: 1024px) { /* Laptop */ }
@media (min-width: 1025px) { /* Desktop */ }
```

### Performance Mobile
- 📦 **Bundle Minimizzato** (CSS ~45KB, JS ~35KB)
- 🗜️ **Compressione Gzip** automatica
- 🚀 **Cache Intelligente** per risposte AJAX
- 📱 **PWA Ready** con service worker opzionale

## 🔒 Sicurezza

### Protezioni Implementate
- 🛡️ **CSRF Protection** su tutti i form
- 🔐 **SQL Injection** prevenzione con prepared statements  
- 🧹 **XSS Protection** sanitizzazione input/output
- 📁 **File Upload** validazione estensioni e mime-type
- 🔑 **Nonce Verification** per chiamate AJAX

### File di Sicurezza
Tutti i directory hanno file `index.php` per prevenire directory listing:
```php
<?php
header('Location: ../');
exit;
```

### Validazione Dati
```php
// Validazione input prezzi
if (!Validate::isPrice($price)) {
    throw new PrestaShopException('Invalid price format');
}

// Sanitizzazione attributi
$attribute_name = Tools::purifyHTML($attribute_name);
$attribute_name = Tools::substr($attribute_name, 0, 255);
```

## 🚀 Performance

### Ottimizzazioni Implementate
- ⚡ **Caching Strategy** multilivello (database, file, memory)
- 🗜️ **Asset Minification** CSS/JS automatica
- 📱 **Lazy Loading** immagini e componenti
- 🔄 **AJAX Debouncing** per ridurre richieste server
- 📊 **Database Indexing** ottimizzato per query frequenti

### Cache Configuration
```php
// Configuration cache
Configuration::set('CUSTOM_PRODUCT_CACHE_ENABLED', true);
Configuration::set('CUSTOM_PRODUCT_CACHE_LIFETIME', 3600);

// Pricing cache
$cache_key = 'custom_pricing_' . $id_product . '_' . md5(serialize($params));
Cache::store($cache_key, $pricing_data, 3600);
```

### Database Optimization
```sql
-- Indici ottimizzati
CREATE INDEX idx_product_active ON ps_custom_product_config (id_product, active);
CREATE INDEX idx_pricing_lookup ON ps_custom_product_pricing (id_product, quantity, delivery_days);
CREATE INDEX idx_attributes_type ON ps_custom_product_attribute (id_product, attribute_type, active);
```

## 🧪 Testing

### Test Suite Inclusi
- ✅ **Unit Tests** per classi PHP
- 🔍 **Integration Tests** per AJAX endpoints  
- 📱 **Responsive Tests** cross-device
- ♿ **Accessibility Tests** WCAG 2.1 compliance
- 🚀 **Performance Tests** Core Web Vitals

### Eseguire i Test
```bash
# Test unitari
./vendor/bin/phpunit tests/Unit/

# Test integrazione
./vendor/bin/phpunit tests/Integration/

# Test JavaScript
npm test

# Test performance
lighthouse http://yourstore.com/product/123 --view
```

### Test Manuali
1. **Configurazione Base**: Abilita modulo, configura prodotto
2. **Selezione Attributi**: Testa tutti i tipi di attributi
3. **Calcolo Prezzi**: Verifica prezzi con diverse configurazioni
4. **Date Consegna**: Controlla calcoli festività/weekend
5. **Mobile**: Testa su diversi dispositivi
6. **Carrello**: Verifica aggiunta e checkout

## 📈 Analytics & Monitoraggio

### Metriche Tracciate
- 📊 **Configurazioni Popolari** - attributi più selezionati
- 💰 **Valore Medio Ordine** - impatto personalizzazione
- 🔄 **Tasso Conversione** - configurator vs checkout
- ⏱️ **Tempo Configurazione** - UX optimization data
- 📱 **Device Usage** - mobile vs desktop usage

### Google Analytics Integration
```javascript
// Event tracking personalizzazioni
gtag('event', 'custom_product_configured', {
    'product_id': productId,
    'total_price': finalPrice,
    'configuration_time': timeSpent,
    'attributes_selected': attributeCount
});
```

### Dashboard Metriche
Il modulo include dashboard admin con:
- 📈 Grafici utilizzo configurazioni
- 🏆 Top 10 configurazioni popolari  
- 💡 Suggerimenti ottimizzazione
- 📊 Report esportabili CSV/PDF

## 🛠️ Troubleshooting

### Problemi Comuni

#### 1. **Modulo non si installa**
```bash
# Verifica permessi
chmod 755 modules/customproductconfig/
chmod 644 modules/customproductconfig/*.php

# Verifica compatibilità PHP
php -v  # deve essere >= 7.4

# Controlla log errori
tail -f var/logs/app/prod.log
```

#### 2. **AJAX non funziona**
```javascript
// Verifica configurazione
console.log(window.customProductConfig);

// Controlla risposta server
fetch('/modules/customproductconfig/controllers/front/ajax.php')
    .then(r => r.json())
    .then(console.log);
```

#### 3. **Griglia prezzi vuota**
1. Verifica configurazione prodotto attiva
2. Controlla presenza dati in `ps_custom_product_pricing`
3. Rigenera matrice prezzi da admin
4. Verifica JavaScript console per errori

#### 4. **Date consegna sbagliate**
1. Controlla configurazione giorni lavorativi
2. Verifica orario limite ordini
3. Aggiorna elenco festività
4. Test con diversi timezone

### Debug Mode

Abilita debug mode per diagnostica:
```php
// In admin configurazione
Configuration::updateValue('CUSTOM_PRODUCT_DEBUG_MODE', true);

// Nel codice
if (Configuration::get('CUSTOM_PRODUCT_DEBUG_MODE')) {
    error_log('Custom Product Debug: ' . json_encode($data));
}
```

### Log Files
- 📄 `var/logs/customproductconfig.log` - Log modulo
- 📄 `var/logs/app/prod.log` - Log generale PrestaShop
- 📄 `var/logs/app/dev.log` - Log sviluppo (se attivo)

## 🔄 Aggiornamenti

### Processo di Aggiornamento
1. **Backup** database e file modulo
2. **Download** nuova versione
3. **Upload** file aggiornati
4. **Esegui** script migrazione se necessario
5. **Test** funzionalità principali

### Migrazione Dati
Il modulo include script automatici per migrazioni:
```sql
-- Esempio migrazione v1.0 → v1.1
ALTER TABLE ps_custom_product_config 
ADD COLUMN new_feature VARCHAR(255) DEFAULT NULL;

UPDATE ps_custom_product_config 
SET new_feature = 'default_value' 
WHERE new_feature IS NULL;
```

### Changelog Automatico
- 📋 Change log automatico da git commits
- 🏷️ Semantic versioning (MAJOR.MINOR.PATCH)
- 📢 Notifiche automatiche aggiornamenti disponibili

## 🤝 Supporto & Community

### Canali di Supporto
- 📧 **Email**: support@yourcompany.com
- 💬 **Forum**: community.yourcompany.com
- 📖 **Documentazione**: docs.yourcompany.com
- 🐛 **Bug Report**: github.com/yourcompany/customproductconfig/issues

### Contribuire al Progetto
1. **Fork** il repository
2. **Crea** feature branch (`git checkout -b feature/AmazingFeature`)
3. **Commit** modifiche (`git commit -m 'Add AmazingFeature'`)
4. **Push** al branch (`git push origin feature/AmazingFeature`)
5. **Apri** Pull Request

### Coding Standards
```php
// PSR-12 compliance
<?php

declare(strict_types=1);

class CustomProductExample
{
    private string $property;

    public function method(int $parameter): bool
    {
        return true;
    }
}
```

## 📄 Licenza & Copyright

### Licenza MIT
```
Copyright (c) 2024 Your Company

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.
```

### Riconoscimenti
- 🙏 **PrestaShop Team** per il framework eccellente
- 🎨 **UI/UX Inspiration** da PixartPrinting
- 📚 **Open Source Libraries** utilizzate nel progetto
- 👥 **Community** per feedback e contributi

---

## 🎯 Roadmap Sviluppo Futuro

### v1.1 (Q2 2024)
- [ ] 🌍 **Multi-lingua** supporto completo
- [ ] 📧 **Email Templates** personalizzate per ordini
- [ ] 📊 **Advanced Analytics** dashboard
- [ ] 🔌 **API REST** completa

### v1.2 (Q3 2024)
- [ ] 🛒 **Bulk Orders** configurazione multipla
- [ ] 🎨 **Theme Builder** per personalizzazione UI
- [ ] 📱 **Progressive Web App** funzionalità
- [ ] 🤖 **AI Suggestions** per configurazioni ottimali

### v2.0 (Q4 2024)
- [ ] ☁️ **Cloud Sync** configurazioni multi-store
- [ ] 🔄 **Real-time Collaboration** admin
- [ ] 📈 **Advanced Reporting** con machine learning
- [ ] 🌐 **Marketplace Integration** per template condivisi

---

## 📞 Contatti

**Your Company**
- 🌐 Website: https://yourcompany.com
- 📧 Email: info@yourcompany.com
- 📱 Phone: +39 123 456 7890
- 📍 Address: Via Example 123, 20100 Milano, Italia

**Team Sviluppo**
- 👨‍💻 Lead Developer: developer@yourcompany.com
- 🎨 UI/UX Designer: design@yourcompany.com
- 🧪 QA Tester: qa@yourcompany.com

---

*Documentazione aggiornata: Dicembre 2024 | Versione Modulo: 1.0.0*