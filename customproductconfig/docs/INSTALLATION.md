# 📦 Guida Installazione Dettagliata

## Custom Product Configurator for PrestaShop 8

Questa guida ti accompagnerà passo-passo nell'installazione e configurazione del modulo Custom Product Configurator.

---

## 🎯 Prima dell'Installazione

### ✅ Checklist Requisiti di Sistema

Prima di procedere, assicurati che il tuo sistema soddisfi tutti i requisiti:

#### **PrestaShop**
- [ ] PrestaShop 8.0.0 o superiore
- [ ] Accesso admin al back-office
- [ ] Permessi di installazione moduli attivi

#### **Server Web**
- [ ] Apache 2.4+ o Nginx 1.18+
- [ ] PHP 7.4, 8.0, 8.1, 8.2 o 8.3
- [ ] MySQL 5.7+ o MariaDB 10.3+
- [ ] SSL/HTTPS attivo (raccomandato)

#### **Estensioni PHP Richieste**
```bash
# Verifica estensioni installate
php -m | grep -E "(json|curl|mbstring|intl|gd|zip|xml)"
```

Estensioni necessarie:
- [ ] `json` - Gestione dati AJAX
- [ ] `curl` - Chiamate HTTP
- [ ] `mbstring` - Supporto UTF-8
- [ ] `intl` - Internazionalizzazione
- [ ] `gd` o `imagick` - Elaborazione immagini
- [ ] `zip` - Gestione archivi
- [ ] `xml` - Parsing configurazioni

#### **Configurazione PHP**
```ini
; Valori minimi raccomandati in php.ini
memory_limit = 256M
max_execution_time = 300
upload_max_filesize = 50M
post_max_size = 50M
max_input_vars = 3000
```

#### **Database**
- [ ] Utente MySQL con privilegi `CREATE`, `ALTER`, `DROP`, `INSERT`, `UPDATE`, `DELETE`
- [ ] Spazio disponibile: minimo 50MB
- [ ] Encoding UTF-8 (`utf8mb4_unicode_ci`)

---

## 📁 Metodi di Installazione

### Metodo 1: Upload ZIP via Admin (⭐ Raccomandato)

#### **Step 1: Preparazione File**
1. Scarica `customproductconfig.zip`
2. Verifica dimensione file (~200KB)
3. NON estrarre l'archivio

#### **Step 2: Upload via Admin**
1. **Accedi** al back-office PrestaShop
2. **Naviga** a `Moduli` → `Module Manager`
3. **Clicca** su "Carica un modulo" (in alto a destra)
4. **Trascina** il file ZIP nell'area di upload OPPURE
5. **Clicca** "seleziona file" e scegli `customproductconfig.zip`

#### **Step 3: Installazione**
1. **Attendi** il caricamento (barra di progresso)
2. Il modulo apparirà nella lista con pulsante "Installa"
3. **Clicca** "Installa"
4. **Attendi** l'installazione (può richiedere 30-60 secondi)

#### **Step 4: Verifica Installazione**
```
✅ Messaggio di successo: "Modulo installato con successo"
✅ Modulo presente in: Moduli → Moduli installati
✅ Voce menu: Moduli → Custom Product Configurator
```

---

### Metodo 2: Upload FTP Manuale

#### **Step 1: Estrazione**
```bash
# Estrai il modulo
unzip customproductconfig.zip
# Risultato: cartella customproductconfig/
```

#### **Step 2: Upload FTP**
```bash
# Via client FTP (FileZilla, WinSCP, etc.)
# Carica cartella customproductconfig/ in:
/path/to/prestashop/modules/

# Via comando SCP
scp -r customproductconfig/ user@server:/path/to/prestashop/modules/
```

#### **Step 3: Permessi File**
```bash
# Imposta permessi corretti
chmod -R 755 modules/customproductconfig/
chmod 644 modules/customproductconfig/*.php
chmod 644 modules/customproductconfig/config.xml
```

#### **Step 4: Installazione da Admin**
1. **Vai** a `Moduli` → `Module Manager`
2. **Cerca** "Custom Product Configurator"
3. **Clicca** "Installa" quando appare

---

### Metodo 3: CLI per Sviluppatori

#### **Step 1: Setup**
```bash
# Posizionati nella directory PrestaShop
cd /path/to/prestashop

# Verifica CLI disponibile
ls bin/console
```

#### **Step 2: Installazione**
```bash
# Installa modulo via CLI
php bin/console prestashop:module:install customproductconfig

# Verifica stato
php bin/console prestashop:module:list | grep customproductconfig
```

#### **Step 3: Configurazione CLI**
```bash
# Abilita modulo
php bin/console prestashop:module:enable customproductconfig

# Reset cache
php bin/console cache:clear --env=prod
```

---

## ⚙️ Configurazione Post-Installazione

### 🎛️ Step 1: Configurazione Globale

**Percorso**: `Moduli` → `Custom Product Configurator` → `Configura`

#### **Giorni di Consegna**
```
Campo: Giorni di Consegna
Valore: 3,5,7,10,14
Descrizione: Opzioni disponibili per i clienti
```

#### **Festività**
```
Campo: Festività
Valore: 2024-12-25
        2024-12-26
        2025-01-01
        2025-01-06
Formato: YYYY-MM-DD (una per riga)
```

#### **Orario Limite**
```
Campo: Orario Limite Ordini
Valore: 16:00
Descrizione: Ordini dopo le 16 iniziano produzione giorno dopo
```

#### **Giorni Lavorativi**
```
Campo: Giorni Lavorativi
Valore: 1,2,3,4,5
Significato: 1=Lunedì, 2=Martedì, ..., 7=Domenica
```

#### **Salvataggio**
1. **Clicca** "Salva Impostazioni"
2. **Verifica** messaggio di successo
3. **Testa** calcolo date consegna

---

### 📦 Step 2: Configurazione Primo Prodotto

#### **Selezione Prodotto**
1. **Vai** a `Catalogo` → `Prodotti`
2. **Seleziona** un prodotto esistente o creane uno nuovo
3. **Clicca** "Modifica"

#### **Abilitazione Configuratore**
1. **Cerca** tab "Custom Product Configurator"
2. **Abilita** "Configurazione Personalizzata" → SÌ
3. **Imposta** quantità minima (es. 50)
4. **Imposta** prezzo base (es. 100.00€)

#### **Configurazione Attributi**

**Formato Scatola:**
```
Nome: Piccolo (15x15cm)    | Moltiplicatore: 1.0
Nome: Medio (20x20cm)      | Moltiplicatore: 1.2  
Nome: Grande (25x25cm)     | Moltiplicatore: 1.5
```

**Tipo di Carta:**
```
Nome: Standard 300gr       | Moltiplicatore: 1.0
Nome: Premium 400gr        | Moltiplicatore: 1.3
Nome: Luxury 500gr         | Moltiplicatore: 1.6
```

**Finitura:**
```
Nome: Opaca               | Moltiplicatore: 1.0
Nome: Lucida              | Moltiplicatore: 1.1
Nome: Soft Touch          | Moltiplicatore: 1.25
```

**Colori di Stampa:**
```
Nome: 1 Colore            | Moltiplicatore: 1.0
Nome: 2 Colori            | Moltiplicatore: 1.4
Nome: Quadricromia        | Moltiplicatore: 2.0
```

#### **Generazione Matrice Prezzi**
1. **Clicca** "Genera Matrice" 
2. **Conferma** sovrascrittura
3. **Attendi** generazione automatica
4. **Personalizza** prezzi se necessario

#### **Salvataggio Configurazione**
1. **Clicca** "Salva Configurazione"
2. **Verifica** messaggio di successo
3. **Testa** frontend prodotto

---

## 🧪 Testing Post-Installazione

### ✅ Test Funzionalità Base

#### **Test 1: Configuratore Frontend**
1. **Vai** alla pagina prodotto configurato
2. **Verifica** presenza sezione configuratore
3. **Seleziona** tutti gli attributi
4. **Verifica** aggiornamento prezzo in tempo reale

#### **Test 2: Griglia Prezzi**
1. **Clicca** "Mostra griglia prezzi"
2. **Verifica** caricamento tabella
3. **Seleziona** diverse combinazioni quantità/giorni
4. **Controlla** calcolo sconti automatici

#### **Test 3: Date di Consegna**
1. **Verifica** calcolo date corrette
2. **Controlla** esclusione weekend (se configurato)
3. **Testa** gestione festività
4. **Verifica** orario limite

#### **Test 4: Carrello**
1. **Completa** configurazione prodotto
2. **Aggiungi** al carrello
3. **Verifica** dettagli personalizzazione
4. **Procedi** al checkout

---

### 🔧 Test Funzionalità Admin

#### **Test 1: Import/Export CSV**
1. **Scarica** CSV esempio da `/examples/sample_pricing.csv`
2. **Importa** via admin panel
3. **Verifica** aggiornamento prezzi
4. **Esporta** per controllo

#### **Test 2: Copia Configurazione**
1. **Configura** secondo prodotto
2. **Usa** funzione "Copia Configurazione"
3. **Verifica** trasferimento attributi e prezzi

#### **Test 3: Test Calculator**
1. **Usa** test calculator in admin
2. **Inserisci** quantità e giorni
3. **Verifica** calcolo prezzo corretto

---

## 🚨 Risoluzione Problemi Comuni

### ❌ Problema: Modulo Non Si Installa

#### **Diagnostica**
```bash
# Verifica permessi
ls -la modules/customproductconfig/
# Output atteso: rwxr-xr-x per directory, rw-r--r-- per file

# Verifica PHP
php -v
# Versione deve essere >= 7.4

# Controlla log errori
tail -f var/logs/app/prod.log
```

#### **Soluzioni**
```bash
# Correggi permessi
chmod 755 modules/customproductconfig/
chmod -R 644 modules/customproductconfig/*.php

# Verifica spazio disco
df -h

# Controlla memoria PHP
php -i | grep memory_limit
```

---

### ❌ Problema: AJAX Non Funziona

#### **Diagnostica Browser**
```javascript
// Apri Console Sviluppatore (F12)
// Verifica errori JavaScript
console.log(window.customProductConfig);

// Testa endpoint AJAX
fetch('/modules/customproductconfig/controllers/front/ajax.php', {
    method: 'POST',
    body: 'action=test'
}).then(r => console.log(r.status));
```

#### **Diagnostica Server**
```bash
# Verifica log Apache/Nginx
tail -f /var/log/apache2/error.log
tail -f /var/log/nginx/error.log

# Testa URL direttamente
curl -X POST "https://tuosito.com/modules/customproductconfig/controllers/front/ajax.php" \
     -d "action=test"
```

#### **Soluzioni**
1. **Verifica** URL friendly attivo
2. **Controlla** .htaccess per rewrite rules
3. **Disabilita** cache browser/CDN temporaneamente
4. **Verifica** configurazione CORS se multi-dominio

---

### ❌ Problema: Griglia Prezzi Vuota

#### **Diagnostica Database**
```sql
-- Verifica tabelle create
SHOW TABLES LIKE 'ps_custom_product_%';

-- Controlla dati prodotto
SELECT * FROM ps_custom_product_config WHERE id_product = 123;
SELECT * FROM ps_custom_product_pricing WHERE id_product = 123;
```

#### **Soluzioni**
1. **Rigenera** matrice prezzi da admin
2. **Verifica** configurazione prodotto attiva
3. **Controlla** giorni di consegna configurati
4. **Importa** CSV di esempio

---

### ❌ Problema: Date Consegna Sbagliate

#### **Diagnostica Configurazione**
```php
// In configurazione globale, verifica:
// Giorni lavorativi: 1,2,3,4,5 (Lun-Ven)
// Orario limite: 16:00
// Festività: formato YYYY-MM-DD
```

#### **Test Manuale**
```javascript
// Console browser, testa calcolo
const calculator = new Date();
calculator.setDate(calculator.getDate() + 3);
console.log('Test date:', calculator.toLocaleDateString('it-IT'));
```

#### **Soluzioni**
1. **Aggiorna** lista festività italiane
2. **Verifica** timezone server
3. **Controlla** formato date in database
4. **Resetta** cache date consegna

---

## 📊 Monitoraggio Post-Installazione

### 📈 Metriche da Monitorare

#### **Performance**
```bash
# Tempo caricamento pagina prodotto
curl -w "@curl-format.txt" -o /dev/null -s "https://tuosito.com/prodotto-test"

# Risposta AJAX
time curl -X POST "https://tuosito.com/modules/customproductconfig/controllers/front/ajax.php" \
          -d "action=calculatePrice&id_product=123&quantity=500&delivery_days=5"
```

#### **Database**
```sql
-- Verifica crescita tabelle
SELECT 
    TABLE_NAME,
    ROUND(((DATA_LENGTH + INDEX_LENGTH) / 1024 / 1024), 2) AS 'Size MB'
FROM information_schema.TABLES 
WHERE TABLE_SCHEMA = 'prestashop_db' 
AND TABLE_NAME LIKE 'ps_custom_product_%';
```

#### **Utilizzo**
```sql
-- Configurazioni più popolari
SELECT attribute_type, attribute_name, COUNT(*) as usage_count
FROM ps_custom_cart_product ccp
JOIN ps_custom_product_attribute cpa ON ccp.id_product = cpa.id_product
GROUP BY attribute_type, attribute_name
ORDER BY usage_count DESC;
```

---

### 🔔 Alert Automatici

#### **Setup Monitoring**
```bash
# Cron job per verifiche automatiche
# Aggiungi a crontab -e

# Verifica ogni ora se modulo risponde
0 * * * * curl -s "https://tuosito.com/modules/customproductconfig/controllers/front/ajax.php" \
          -d "action=test" | grep -q "success" || \
          echo "Custom Product Config DOWN" | mail -s "Alert" admin@tuosito.com

# Backup giornaliero configurazioni
0 2 * * * mysqldump prestashop_db ps_custom_product_config ps_custom_product_attribute \
          ps_custom_product_pricing > /backup/custom_product_$(date +\%Y\%m\%d).sql
```

---

## 🎓 Formazione Team

### 👥 Training Amministratori

#### **Checklist Competenze**
- [ ] **Configurazione prodotti** - abilita/disabilita configuratore
- [ ] **Gestione attributi** - aggiungi/modifica/rimuovi attributi
- [ ] **Matrice prezzi** - genera automatica e personalizza
- [ ] **Import/Export CSV** - aggiornamenti massa prezzi
- [ ] **Test funzionalità** - verifica calcoli e configurazioni

#### **Documentazione Rapida**
```
📋 QUICK REFERENCE ADMIN

Nuovo Prodotto Configurabile:
1. Prodotti → Modifica → Tab Custom Product Config
2. Abilita configurazione → SÌ
3. Imposta quantità minima e prezzo base
4. Aggiungi attributi per categoria
5. Genera matrice prezzi automatica
6. Testa frontend

Aggiornamento Prezzi:
1. Prepara CSV: quantità,giorni,prezzo,sconto
2. Importa via "Importa CSV"
3. Verifica prezzi aggiornati
4. Testa calcoli frontend

Copia Configurazione:
1. Seleziona prodotto sorgente
2. Clicca "Copia Configurazione"  
3. Personalizza se necessario
4. Salva configurazione
```

---

### 👨‍💻 Training Sviluppatori

#### **API Reference Rapido**
```javascript
// Calcolo prezzo
fetch('/modules/customproductconfig/controllers/front/ajax.php', {
    method: 'POST',
    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
    body: 'action=calculatePrice&id_product=123&quantity=500&delivery_days=5&attributes=' + 
          JSON.stringify({formato: 'Medio', carta: 'Premium'})
});

// Aggiunta carrello
fetch('/modules/customproductconfig/controllers/front/ajax.php', {
    method: 'POST',
    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
    body: 'action=addToCart&id_product=123&quantity=500&delivery_days=5&attributes=' +
          JSON.stringify({formato: 'Medio', carta: 'Premium'})
});
```

#### **Personalizzazione CSS**
```css
/* Override colori brand */
.custom-configurator-wrapper {
    --primary-color: #your-brand-color;
    --secondary-color: #your-secondary-color;
}

/* Personalizza griglia prezzi */
.pricing-grid .price-cell.selected {
    background: linear-gradient(135deg, #your-primary, #your-secondary);
}
```

---

## 📞 Supporto Tecnico

### 🆘 Quando Contattare il Supporto

#### **Problemi Critici**
- ❌ Modulo non si installa dopo multiple prove
- ❌ Errori database durante installazione
- ❌ Carrello non funziona con prodotti configurati
- ❌ Performance drasticamente degradate

#### **Informazioni da Fornire**
```
🔧 TEMPLATE SUPPORTO

Versione PrestaShop: 8.x.x
Versione PHP: 8.x
Versione MySQL: 8.x
Versione Modulo: 1.0.0
Tema attivo: [nome tema]
Altri moduli installati: [lista moduli che modificano prodotti/carrello]

Problema riscontrato:
[Descrizione dettagliata]

Step per riprodurre:
1. [Step 1]
2. [Step 2]
3. [Step 3]

Errori nel log:
[Copia da var/logs/app/prod.log]

Screenshot:
[Allega screenshot se applicabile]
```

### 📧 Contatti Supporto
- **Email Tecnico**: tech-support@yourcompany.com
- **Email Commerciale**: sales@yourcompany.com
- **Documentazione**: https://docs.yourcompany.com/customproductconfig
- **Community Forum**: https://community.yourcompany.com

---

## ✅ Checklist Installazione Completata

### 🎯 Verifica Finale

- [ ] **Modulo installato** e attivo in admin
- [ ] **Configurazione globale** completata (giorni, festività, orari)
- [ ] **Primo prodotto** configurato con attributi
- [ ] **Matrice prezzi** generata e testata
- [ ] **Frontend** mostra configuratore su prodotto test
- [ ] **Calcolo prezzi** funziona in tempo reale
- [ ] **Date consegna** calcolate correttamente
- [ ] **Carrello** accetta prodotti configurati
- [ ] **Admin panel** accessibile e funzionale
- [ ] **Test dispositivi** mobile completati

### 🚀 Prossimi Step

1. **Configura** tutti i prodotti necessari
2. **Forma** il team amministrativo
3. **Monitora** performance e utilizzo
4. **Pianifica** backup periodici
5. **Considera** personalizzazioni avanzate

---

*Guida aggiornata: Dicembre 2024 | Versione: 1.0.0*

**🎉 Congratulazioni! Il tuo Custom Product Configurator è ora operativo!**