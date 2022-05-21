<?php
class DB
{

    public function __construct()
    {
        if (!isset($this->db)) {
            $datos = $this->datosConexion();
            foreach ($datos as $value) {
                $dbHost     = $value['host'];
                $dbUsername = $value['user'];
                $dbPassword = $value['password'];
                $dbName     = $value['database'];
            }
            // Connect to the database
            $conn = new mysqli($dbHost, $dbUsername, $dbPassword, $dbName);
            if ($conn->connect_error) {
                die("Failed to connect with MySQL: " . $conn->connect_error);
            } else {
                $this->db = $conn;
            }
        }
    }

    private function datosConexion()
    {
        $dirname = dirname(__FILE__);
        $json = file_get_contents($dirname . '/config.json');
        $datosMySQL = json_decode($json, true);
        return ["datosMySQL" => $datosMySQL['datosMySQL']];
    }

    public function is_token_empty()
    {
        $result = $this->db->query("SELECT id FROM tokens WHERE provider = 'google'");
        if ($result->num_rows) {
            return false;
        }

        return true;
    }

    public function get_refersh_token()
    {
        $sql = $this->db->query("SELECT provider_value FROM tokens WHERE provider='google'");
        return $sql->fetch_assoc()['provider_value'];
    }

    public function update_refresh_token($token)
    {
        if ($this->is_token_empty()) {
            $this->db->query("INSERT INTO tokens(provider, provider_value) VALUES('google', '$token')");
        } else {
            $this->db->query("UPDATE tokens SET provider_value = '$token' WHERE provider = 'google'");
        }
    }
}
