# AlphaLabs OAuth2 client

> A PHP OAuth2 API client. Works well with friendofsymfony/oauth2-php specification

This library is build over [Guzzle](https://github.com/guzzle/guzzle) and adds OAuth2 authentication mechanisms to make secured and user-oriented API calls.

The API client follow the OAuth2 specification which are applied in the [friendofsymfony/oauth2-php](https://github.com/FriendsOfSymfony/oauth2-php) library and the [friendsofsymfony/oauth-server-bundle](https://github.com/FriendsOfSymfony/FOSOAuthServerBundle).

For the moment, the library provides the following features:

- **Oauth2 transparent authentication**: Make the initial resource request, the library will request access token if needed transparently.
- **Resource deserialization**: An instance of the [JMS Serializer](https://github.com/schmittjoh/serializer) can be provided to the API client. Thus, if the target ressource class is linked to the request, the API client will try to deserialize the response data into the target object.

## Installation

Adds the library in your `composer.json` file:

````json
"require": {
    "alphalabs/oauth2-client": "1.0@dev"
}
````

Don't forget to update your dependencies with `composer update`

## Usage

Starts by create a class which implements `AlphaLabs\OAuth2Client\Model\Security\TokenManager`.
This manager will handle the persistence strategy of the access tokens between requests.

````php
<?php

namespace Foo\Bar;

use AlphaLabs\OAuth2Client\Model\Security\Token;
use AlphaLabs\OAuth2Client\Model\Security\TokenManager;

class MyTokenManager implements TokenManager
{
    public function getUserToken($clientName, $userId) {
        // Retrieve the token linked to the user (for user-oriented API calls).
        // It could be stored in a database, a cache file etc ...

        return $token;
    }

    public function getClientToken($clientName) {
        // Retrieve the token linked to the client (for client-oriented API calls).
        // It could be stored in a database, a cache file etc ...

        return $token;
    }

    public function save($clientName, Token $token) {
        // The type of token (user/client) could be determined with the userId attribute value:
        if ($token->getUserId()) {
            // This is a user-related token
            // Persists the token in a DB, a cache file etc...
        } else {
            // This is a client-related token
            // Persists the token in a DB, a cache file etc...
        }
    }
}
````

Then, you can instanciate an API client and start requesting the API:

````php
<?php

namespace Foo\Bar;

use AlphaLabs\OAuth2Client\OAuth2Client;

class MyClass
{
    public function foo()
    {
        $apiClient = new OAuth2Client(
            'my_api_client'                 // Client name
            'https://api.myproject.com',    // Base API URL
            'my_client_id',                 // The client ID (provided by the API)
            'my_client_secret',             // The client secret key (provided by the API)
            new MyTokenManager(),           // Your custom token manager
            '/oauth/v2/token'               // The URI used to requests access tokens
        );

        $request = new ClientRequest('GET', '/ping');

        // Optionally, an instance of the JMS Serialiser can be injected into the client in order to
        // get an object instead of an associative array:
        $apiClient->setSerializer(JMS\Serializer\SerializerBuilder::create()->build());
        $request->setDeserializationTargetClass('\Foo\Bar\PingResource');

        $pingResult = $apiClient->send();
    }
}
````

## To come

- Better error handling (based on HTTP code)
- Adds possibility to retreive response info (headers, http code) with the response data
- (propose your ideas)

## Credits

- Sylvain Mauduit (@Swop)

## License

MIT
