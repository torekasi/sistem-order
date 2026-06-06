<?php
/**
 * =========================================================
 * UserModel - Model Pengguna
 * =========================================================
 */

require_once BASE_PATH . 'utils/Security.php';

class UserModel {

    private PDO $db;

    public function __construct() {
        $this->db = getDB();
    }

    /**
     * Daftar pengguna baru
     */
    public function register(string $nama, string $email, string $telefon, string $kata_laluan, string $role = 'customer'): int|false {
        try {
            $stmt = $this->db->prepare("INSERT INTO users (nama, email, telefon, kata_laluan, role) VALUES (?, ?, ?, ?, ?)");
            $hash = Security::hashPassword($kata_laluan);
            $stmt->execute([$nama, $email, $telefon, $hash, $role]);
            return (int) $this->db->lastInsertId();
        } catch (PDOException $e) {
            Logger::error("Register gagal: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Login pengguna
     */
    public function login(string $loginId, string $kata_laluan): array|false {
        try {
            $stmt = $this->db->prepare("SELECT * FROM users WHERE (telefon = ? OR email = ?) AND status = 'aktif' LIMIT 1");
            $stmt->execute([$loginId, $loginId]);
            $user = $stmt->fetch();

            if ($user && Security::verifyPassword($kata_laluan, $user['kata_laluan'])) {
                unset($user['kata_laluan']);
                return (array)$user;
            }
            return false;
        } catch (PDOException $e) {
            Logger::error("Login gagal: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Dapatkan pengguna berdasarkan ID
     */
    public function getUserById(int $id): array|false {
        $stmt = $this->db->prepare("SELECT id, nama, email, telefon, role, status, created_at FROM users WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    /**
     * Dapatkan pengguna berdasarkan email
     */
    public function getUserByEmail(string $email): array|false {
        $stmt = $this->db->prepare("SELECT id, nama, email, telefon, role, status FROM users WHERE email = ?");
        $stmt->execute([$email]);
        return $stmt->fetch();
    }

    /**
     * Senarai semua pengguna (admin)
     */
    public function getAllUsers(string $role = ''): array {
        $sql = "SELECT id, nama, email, telefon, role, status, created_at FROM users";
        $params = [];
        if ($role) {
            $sql .= " WHERE role = ?";
            $params[] = $role;
        }
        $sql .= " ORDER BY created_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Verify user password by ID
     */
    public function verifyPassword(int $id, string $password): bool {
        $stmt = $this->db->prepare("SELECT kata_laluan FROM users WHERE id = ?");
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ? Security::verifyPassword($password, $row['kata_laluan']) : false;
    }

    /**
     * Kemaskini pengguna
     */
    public function updateUser(int $id, array $data): bool {
        $fields = [];
        $params = [];
        foreach (['nama', 'email', 'telefon', 'role', 'status', 'kata_laluan'] as $key) {
            if (isset($data[$key])) {
                $fields[] = "{$key} = ?";
                $params[] = $data[$key];
            }
        }
        if (empty($fields)) return false;
        $params[] = $id;
        $sql = "UPDATE users SET " . implode(', ', $fields) . " WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }

    /**
     * Padam pengguna
     */
    public function deleteUser(int $id): bool {
        try {
            $stmt = $this->db->prepare("DELETE FROM users WHERE id = ?");
            return $stmt->execute([$id]);
        } catch (PDOException $e) {
            Logger::error("Delete user gagal: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Semak email wujud
     */
    public function emailExists(string $email, int $excludeId = 0): bool {
        $sql = "SELECT COUNT(*) FROM users WHERE email = ?";
        $params = [$email];
        if ($excludeId > 0) {
            $sql .= " AND id != ?";
            $params[] = $excludeId;
        }
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn() > 0;
    }
}
