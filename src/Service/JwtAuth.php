<?php

namespace App\Service;

use Doctrine\Persistence\ManagerRegistry;
use Exception;
use Firebase\JWT\JWT;
use App\Entity\User;

class JwtAuth
{
    private $manager;

    private $key;

    public function __construct(ManagerRegistry $manager)
    {
        $this->manager = $manager;
        $this->key = "SECRET";
    }

    /**
     * @param \App\Entity\User $user_to_login
     * @param bool $send_with_token
     * @return array
     */
    public function signup(User $user_to_login, bool $send_with_token)
    {
        $user = $this->manager->getRepository(User::class)->findOneBy([
            "email" => $user_to_login->getEmail(),
            "password" => $user_to_login->getPassword(),
        ]);
        if (isset($user)) {
            $token = [];
            $token["user"] = $user;
            $token["iat"] = time();
            $token["exp"] = time() + (7 * 24 * 60 * 60);

            $jwt = JWT::encode($token, $this->key);
            //Para el decode -> JWT::decode($token, $this->key, ["HS256"]) y devolverá fleje de excepciones si lo queremos comprobar, así que podemos usar eso para luego el front. Chachi.
            if ($send_with_token) {
                return [
                    "decoded_data" => JWT::decode($jwt, $this->key, ["HS256"]),
                    "token" => $jwt,
                ];
            }

            return [
                "decoded_data" => JWT::decode($jwt, $this->key, ["HS256"]),
            ];
        }

        return [];
    }

    /**
     * @param string $jwt token to be checked
     * @return object|null
     *
     * Comprueba que un token jwt es válido.
     * Devuelve el token decodificado si va bien o null si ha habido algún problema
     */
    public function isTokenValid(string $jwt)
    {
        try {
            $decoded_token = JWT::decode($jwt, $this->key, ["HS256"]);
            if ($decoded_token->exp < time() || ! isset($decoded_token->user)) {
                return null;
            }

            return $decoded_token;
        } catch (Exception $ex) {
            return null;
        }
    }
}
