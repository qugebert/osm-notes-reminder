Um die OSM-API nutzen zu können muss eine Anwendung unter https://www.openstreetmap.org/oauth2/applications registriert werden.

Da wir keine öffentlich sichtbare Weiterleitungs-URL anbieten können, muss als "Weiterleitungs-URI" der Platzhalter 
`
urn:ietf:wg:oauth:2.0:oob
`
verwendet werden.

Nötige Berechtigungen

[x] Notizen bearbeiten (Um Hinweise zu kommentieren / wieder zu öffnen) \
[x] Lesen, Aktualisieren des Status und Löschen von Benutzernachrichten (Damit Nutzer per Nachricht eine Auflistung anfragen können) \
[x] Private Nachrichten an andere Benutzer senden (Damit Nutzer per Nachricht eine Auflistung anfragen können)

### Vorgehen mit Platzhalter

1. **Aufruf der Authentifizierungsseite mit einem Browser:**
   ```plaintext
   https://www.openstreetmap.org/oauth2/authorize?response_type=code&client_id=DEINE_CLIENT_ID&redirect_uri=urn:ietf:wg:oauth:2.0:oob&scope=write_notes+consume_messages+send_messages
   ```
2. **Mit dem Bot-Benutzer einloggen, man erhält einen Code.**
3. **Hole den Access Token, z.B. über curl:**
   ```bash
   curl -X POST https://www.openstreetmap.org/oauth2/token \
     -d "grant_type=authorization_code" \
     -d "code=ERHALTENER_CODE" \
     -d "client_id=DEINE_CLIENT_ID" \
     -d "client_secret=DEIN_CLIENT_SECRET" \
     -d "redirect_uri=urn:ietf:wg:oauth:2.0:oob"```

4. Die nötigen Daten in einer Datei speichern (diese Datei muss dann im Container als secret 'oauth2_notes_resminder' eingebunden werden)
```plaintext
{"api_base_url":"https://api.openstreetmap.org/api/0.6/",
"bearer":"TOKEN_AUS_SCHRITT_3"}

