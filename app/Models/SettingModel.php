<?php
/**
 * SIGR - Setting Model
 */

namespace Models;

class SettingModel extends \Core\Model
{
    /**
     * Obtenir tout
     */
    public function getAll(): array
    {
        $settings = $this->db->query("SELECT * FROM settings");
        $result = [];
        foreach ($settings as $s) {
            $result[$s['setting_key']] = $this->castValue($s['setting_value'], $s['setting_type']);
        }
        return $result;
    }
    
    /**
     * Obtenir par clé
     */
    public function get(string $key, $default = null)
    {
        $s = $this->db->queryOne("SELECT * FROM settings WHERE setting_key = ?", [$key]);
        if (!$s) return $default;
        
        return $this->castValue($s['setting_value'], $s['setting_type']);
    }
    
    /**
     * Mettre à jour (ou insérer)
     */
    public function update(string $key, string $value): bool
    {
        $exists = $this->db->queryOne("SELECT id FROM settings WHERE setting_key = ?", [$key]);
        if ($exists) {
            $this->db->update('settings', ['setting_value' => $value], 'setting_key = ?', [$key]);
        } else {
            $this->db->insert('settings', ['setting_key' => $key, 'setting_value' => $value, 'setting_type' => 'string']);
        }
        return true;
    }
    
    private function castValue($value, $type)
    {
        return match($type) {
            'number' => (float) $value,
            'boolean' => in_array(strtolower($value), ['1', 'true', 'yes', 'on']),
            'json' => json_decode($value, true),
            default => $value
        };
    }
}
