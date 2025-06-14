Wenn bei der Registrierung eines Clients für die OSM OAuth2-API ein Redirect-URI verlangt wird, aber du keine echte URI verwenden möchtest, kannst du einen Platzhalter angeben, der akzeptiert wird. Oft wird ein solcher Platzhalter für lokale Tests oder spezielle Anwendungen genutzt.

Der häufig verwendete Platzhalter für Redirect-URIs ist:

```plaintext
urn:ietf:wg:oauth:2.0:oob
```

### Bedeutung des Platzhalters
- **`urn:ietf:wg:oauth:2.0:oob`**: Dies bedeutet "Out of Band" und signalisiert der API, dass keine echte Weiterleitung erforderlich ist. Stattdessen wird der Benutzer nach der Authentifizierung einen Code sehen, den er manuell in deine Anwendung eingeben kann.

### Verwendung des Platzhalters
1. **Bei der Registrierung des Clients:**
   Gib `urn:ietf:wg:oauth:2.0:oob` als Redirect-URI an.
   
2. **Im Authentifizierungsprozess:**
   - Leite den Benutzer zur OSM-Login-Seite weiter.
   - Nach der Anmeldung wird ein Verifizierungscode angezeigt.
   - Der Benutzer gibt diesen Code in deiner Anwendung ein, um den Token-Austausch abzuschließen.

### Beispiel: Ablauf ohne Redirect
1. **Anfrage an die Authentifizierungsseite:**
   ```plaintext
   https://www.openstreetmap.org/oauth2/authorize?response_type=code&client_id=DEINE_CLIENT_ID&redirect_uri=urn:ietf:wg:oauth:2.0:oob&scope=write_notes
   ```
2. **Benutzer loggt sich ein und erhält einen Code.**
3. **Hole den Access Token mit dem Code:**
   Sende eine Anfrage an den Token-Endpunkt:
   ```bash
   curl -X POST https://www.openstreetmap.org/oauth2/token \
     -d "grant_type=authorization_code" \
     -d "code=ERHALTENER_CODE" \
     -d "client_id=DEINE_CLIENT_ID" \
     -d "client_secret=DEIN_CLIENT_SECRET" \
     -d "redirect_uri=urn:ietf:wg:oauth:2.0:oob"
   ```
