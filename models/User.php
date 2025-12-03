<?php
require_once __DIR__ . "/../config/Database.php";

class User
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    // Create user
    public function create($data)
    {
        $sql = "INSERT INTO users (username, password_hash, role, email, display_name)
                VALUES (:username, :password_hash, :role, :email, :display_name)";

        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':username'      => $data['username'],
            ':password_hash' => $data['password_hash'],
            ':role'          => $data['role'],
            ':email'         => $data['email'],
            ':display_name'  => $data['display_name'] ?? null,
        ]);
    }

    // Get all users
    public function all()
    {
        $sql = "SELECT * FROM users ORDER BY user_id DESC";
        return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    // Get single user
    public function find($user_id)
    {
        $sql = "SELECT * FROM users WHERE user_id = :user_id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':user_id' => $user_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Update user
    public function update($user_id, $data)
    {
        $sql = "UPDATE users
                SET username = :username,
                    email = :email,
                    role = :role,
                    display_name = :display_name,
                    updated_at = NOW()
                WHERE user_id = :user_id";

        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':username'     => $data['username'],
            ':email'        => $data['email'],
            ':role'         => $data['role'],
            ':display_name' => $data['display_name'],
            ':user_id'      => $user_id
        ]);
    }

    // Update password
    public function updatePassword($user_id, $newHash)
    {
        $sql = "UPDATE users SET password_hash = :hash WHERE user_id = :user_id";
        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            ':hash' => $newHash,
            ':user_id' => $user_id
        ]);
    }

    // Delete user
    public function delete($user_id)
    {
        $sql = "DELETE FROM users WHERE user_id = :user_id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':user_id' => $user_id]);
    }

    // Login function
    public function login($username)
    {
        $sql = "SELECT * FROM users WHERE username = :username";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':username' => $username]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}