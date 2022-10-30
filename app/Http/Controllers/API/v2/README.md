# API v2
This uses Laravel Passport OAuth Server. The following needs to be in place for the API to work:
1. OAuth Keys in the storage folder.
2. You need an OAuth client in the database.

Both of these are put in place by running migrations. If for some reason you need to recreate the keys or add another client follow these instructions.

###### Create OAuth keys
`php artisan passport:keys`

###### Insert new OAuth client
`php artisan passport:client --password`

It will prompt to fill a client name. This is not import, you can use the default name or make up anything.
On success it will print out the client ID and client Secret. This is what you'll use on the client to make API calls.

Once set up, an example request to the authentication endpoint would look like this:
```
curl --request POST \
  --url http://motionarray.local/api/v2/auth \
  --header 'content-type: application/json' \
  --data '{
	"grant_type": "password",
	"client_id": 1,
	"client_secret": "secret",
	"username": "user@example.com",
	"password": "password123"
}'
```

If your username and password are correct, and your OAuth is set up correctly, you should get a response like this:
```json
{
	"token_type": "Bearer",
	"expires_in": 31536000,
	"access_token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiIsImp0aSI6ImM1NzZmM2M5OGQ5YTg3NDgzMWU1OTcyMzFmNTgwMGU4OTg1M...",
	"refresh_token": "def5020004fc80f6eed18e01fd4c9311bbec7d6dfdf04913286c8042621ef9fc13bf5130fc7adf1ca40cc7fb6aa0...",
	"user": {
		"firstname": "Example",
		"lastname": "User",
		"email": "user@example.com"
	}
}
```

If your username or password was wrong, you'll get:
```json
{
	"error": "invalid_credentials",
	"message": "The user credentials were incorrect."
}
```
If there is something wrong with your OAuth client details you'll get:
```json
{
	"error": "invalid_client",
	"message": "Client authentication failed"
}
```
