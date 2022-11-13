<?php

namespace App\Models;

use CodeIgniter\Model;

class PhotoContentModel extends Model
{
    protected $DBGroup          = 'default';
    protected $table            = 'photo_contents';
    protected $primaryKey       = 'photo_content_id';
    protected $useAutoIncrement = true;
    protected $insertID         = 0;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'photo_content_id',
        'photo_content_host_id',
        'photo_content_level',
        'photo_content_connection',
        'photo_content_url',
        'photo_content_order',
        'photo_content_status',
        'photo_content_activation',
        'photo_content_last_update',
    ];

    // Dates
    protected $useTimestamps = false;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    // Validation
    protected $validationRules      = [];
    protected $validationMessages   = [];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert   = [];
    protected $afterInsert    = [];
    protected $beforeUpdate   = [];
    protected $afterUpdate    = [];
    protected $beforeFind     = [];
    protected $afterFind      = [];
    protected $beforeDelete   = [];
    protected $afterDelete    = [];

    public function get_level1_photo($host_id) {
        $db = \Config\Database::connect();
        $query   = $db->query('SELECT photo_content_id, photo_content_url, photo_content_status
        FROM photo_contents
        WHERE photo_content_level = 1 AND  photo_content_host_id = ' . $host_id);
        $results = $query->getResult();
        foreach($results as &$result) {
            $content_caption_query = $db->query('SELECT IF' . '(ISNULL(content_caption)=1, ' . '""' . ', content_caption) AS content_caption, IF' . '(ISNULL(content_caption_lang)=1, ' . '""' . ', content_caption_lang) AS content_caption_lang FROM content_captions WHERE content_caption_connection_id = ' . $result->photo_content_id . ' AND content_caption_host_id = ' . $host_id . ' AND content_caption_type = 1');
            $content_caption_results = $content_caption_query->getResult();
            $result->content_caption = $content_caption_results;
        }
        return $results;
    }

    public function get_level2_photo($host_id) {
        $db = \Config\Database::connect();
        $query   = $db->query('SELECT photo_content_id, photo_content_connection, photo_content_url, photo_content_status
        FROM photo_contents
        WHERE photo_content_level = "2" AND photo_content_host_id = ' . $host_id);
        $results = $query->getResult();
        foreach($results as &$result) {
            $query = $db->query('SELECT type_mapping_name
            FROM types_mapping
            WHERE type_mapping_code = ' . '"' . $result->photo_content_connection . '"' . ' AND type_mapping_lang="it" AND type_mapping_host_id = ' . $host_id);
            $mapping_names = $query->getResult();
            $type_mapping_names = [];
            foreach($mapping_names as $mapping_name) {
                array_push($type_mapping_names, $mapping_name->type_mapping_name);
            }
            $result->type_mapping_name = $type_mapping_names == null ? [] : $type_mapping_names;

            $content_caption_query = $db->query('SELECT IF' . '(ISNULL(content_caption)=1, ' . '""' . ', content_caption) AS content_caption, IF' . '(ISNULL(content_caption_lang)=1, ' . '""' . ', content_caption_lang) AS content_caption_lang FROM content_captions WHERE content_caption_connection_id = ' . $result->photo_content_id . ' AND content_caption_host_id = ' . $host_id . ' AND content_caption_type = 1');
            $content_caption_results = $content_caption_query->getResult();
            $result->content_caption = $content_caption_results;
        }
        return $results;
    }

    public function get_photo_contents($host_id, $record_status) {
        $db = \Config\Database::connect();
        $query   = $db->query('SELECT photo_contents.photo_content_id, photo_contents.photo_content_url, photo_contents.photo_content_status, IF' . '(ISNULL(content_captions.content_caption)=1, ' . '""' . ', content_captions.content_caption) AS content_caption, IF' . '(ISNULL(content_captions.content_caption_lang)=1, ' . '""' . ', content_captions.content_caption_lang) AS content_caption_lang
        FROM photo_contents LEFT JOIN content_captions ON photo_contents.photo_content_id = content_captions.content_caption_connection_id AND content_captions.content_caption_type="1" AND content_captions.content_caption_host_id = ' . $host_id . '
        WHERE photo_contents.photo_content_status = ' . $record_status . ' AND photo_contents.photo_content_host_id = ' . $host_id);
        $results = $query->getResult();
        foreach($results as &$result) {
            $content_caption = [
                'content_caption' => $result->content_caption,
                'content_caption_lang' => $result->content_caption_lang,
            ];
            $result->content_caption = $content_caption;
        }
        return $results;
    }

    public function is_existed_id($id) {
        $db = \Config\Database::connect();
        $query = $db->query('SELECT photo_content_id FROM photo_contents WHERE photo_content_id = ' . $id);
        $results = $query->getResult();
        return $results;
    }
}
