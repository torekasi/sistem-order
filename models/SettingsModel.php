<?php
/**
 * =========================================================
 * SettingsModel - Pengurusan Tetapan Sistem
 * =========================================================
 */

class SettingsModel {

    private $db;

    public function __construct() {
        $this->db = getDB();
    }

    /**
     * Dapatkan semua tetapan
     */
    public function getAll(): array {
        $stmt = $this->db->query("SELECT * FROM system_settings ORDER BY setting_group, urutan");
        return $stmt->fetchAll();
    }

    /**
     * Dapatkan tetapan mengikut kumpulan
     */
    public function getByGroup(string $group): array {
        $stmt = $this->db->prepare("SELECT * FROM system_settings WHERE setting_group = ? ORDER BY urutan");
        $stmt->execute([$group]);
        return $stmt->fetchAll();
    }

    /**
     * Dapatkan satu tetapan berdasarkan key
     */
    public function get(string $key, string $default = ''): string {
        $stmt = $this->db->prepare("SELECT setting_value FROM system_settings WHERE setting_key = ?");
        $stmt->execute([$key]);
        $result = $stmt->fetch();
        return $result ? ($result['setting_value'] ?? $default) : $default;
    }

    /**
     * Kemaskini satu tetapan
     */
    public function set(string $key, string $value): bool {
        $stmt = $this->db->prepare("UPDATE system_settings SET setting_value = ? WHERE setting_key = ?");
        return $stmt->execute([$value, $key]);
    }

    /**
     * Kemaskini banyak tetapan sekaligus
     */
    public function updateBatch(array $settings): bool {
        $this->db->beginTransaction();
        try {
            $stmt = $this->db->prepare("UPDATE system_settings SET setting_value = ? WHERE setting_key = ?");
            foreach ($settings as $key => $value) {
                $stmt->execute([$value, $key]);
            }
            $this->db->commit();
            return true;
        } catch (PDOException $e) {
            $this->db->rollBack();
            Logger::error("Gagal kemaskini tetapan: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Dapatkan senarai kumpulan tetapan
     */
    public function getGroups(): array {
        $stmt = $this->db->query("SELECT DISTINCT setting_group FROM system_settings ORDER BY setting_group");
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    /**
     * Tambah tetapan baru
     */
    public function addSetting(array $data): bool {
        $stmt = $this->db->prepare(
            "INSERT INTO system_settings (setting_key, setting_value, setting_group, setting_type, setting_label, setting_description, setting_options, urutan)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
        );
        return $stmt->execute([
            $data['setting_key'],
            $data['setting_value'] ?? '',
            $data['setting_group'] ?? 'general',
            $data['setting_type'] ?? 'text',
            $data['setting_label'] ?? $data['setting_key'],
            $data['setting_description'] ?? '',
            $data['setting_options'] ?? null,
            $data['urutan'] ?? 0,
        ]);
    }

    /**
     * Padam tetapan
     */
    public function deleteSetting(string $key): bool {
        $stmt = $this->db->prepare("DELETE FROM system_settings WHERE setting_key = ?");
        return $stmt->execute([$key]);
    }

    /**
     * Export tetapan sebagai array associative
     */
    public function exportAll(): array {
        $all = $this->getAll();
        $result = [];
        foreach ($all as $s) {
            $result[$s['setting_key']] = $s['setting_value'];
        }
        return $result;
    }
}
