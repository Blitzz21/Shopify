<?php
/**
 * Design Model
 * Handles all design-related database operations
 */

class Design {
    private $conn;
    private $table = 'designs';
    
    // Design properties
    public $id;
    public $session_id;
    public $product_id;
    public $original_filename;
    public $stored_filename;
    public $file_path;
    public $preview_path;
    public $file_size;
    public $mime_type;
    public $width;
    public $height;
    public $dpi;
    public $design_config;
    public $status;
    public $error_message;
    public $created_at;
    public $updated_at;
    
    public function __construct($db) {
        $this->conn = $db;
    }
    
    /**
     * Get database connection
     * 
     * Returns the database connection object. This method allows
     * external code to access the connection when needed.
     * 
     * @return PDO Database connection object
     */
    public function getConnection() {
        return $this->conn;
    }
    
    /**
     * Create new design
     */
    public function create() {
        $query = "INSERT INTO " . $this->table . "
                SET session_id = :session_id,
                    product_id = :product_id,
                    original_filename = :original_filename,
                    stored_filename = :stored_filename,
                    file_path = :file_path,
                    preview_path = :preview_path,
                    file_size = :file_size,
                    mime_type = :mime_type,
                    width = :width,
                    height = :height,
                    dpi = :dpi,
                    design_config = :design_config,
                    status = :status,
                    created_at = NOW(),
                    updated_at = NOW()";
        
        $stmt = $this->conn->prepare($query);
        
        // Bind values
        $stmt->bindParam(':session_id', $this->session_id);
        $stmt->bindParam(':product_id', $this->product_id);
        $stmt->bindParam(':original_filename', $this->original_filename);
        $stmt->bindParam(':stored_filename', $this->stored_filename);
        $stmt->bindParam(':file_path', $this->file_path);
        $stmt->bindParam(':preview_path', $this->preview_path);
        $stmt->bindParam(':file_size', $this->file_size);
        $stmt->bindParam(':mime_type', $this->mime_type);
        $stmt->bindParam(':width', $this->width);
        $stmt->bindParam(':height', $this->height);
        $stmt->bindParam(':dpi', $this->dpi);
        $stmt->bindParam(':design_config', $this->design_config);
        $stmt->bindParam(':status', $this->status);
        
        if ($stmt->execute()) {
            $this->id = $this->conn->lastInsertId();
            return true;
        }
        
        return false;
    }
    
    /**
     * Get design by ID
     */
    public function readOne() {
        $query = "SELECT d.*, p.name as product_name 
                  FROM " . $this->table . " d
                  LEFT JOIN products p ON d.product_id = p.id
                  WHERE d.id = :id LIMIT 1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $this->id);
        $stmt->execute();
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($row) {
            $this->mapRowToProperties($row);
            return true;
        }
        
        return false;
    }
    
    /**
     * Get all designs by session ID
     */
    public function readBySession() {
        $query = "SELECT d.*, p.name as product_name 
                  FROM " . $this->table . " d
                  LEFT JOIN products p ON d.product_id = p.id
                  WHERE d.session_id = :session_id 
                  AND d.status != 'deleted'
                  ORDER BY d.created_at DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':session_id', $this->session_id);
        $stmt->execute();
        
        return $stmt;
    }
    
    /**
     * Get designs by product ID
     */
    public function readByProduct($productId) {
        $query = "SELECT d.*, p.name as product_name 
                  FROM " . $this->table . " d
                  LEFT JOIN products p ON d.product_id = p.id
                  WHERE d.product_id = :product_id 
                  AND d.status != 'deleted'
                  ORDER BY d.created_at DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':product_id', $productId);
        $stmt->execute();
        
        return $stmt;
    }
    
    /**
     * Update design
     */
    public function update() {
        $query = "UPDATE " . $this->table . "
                SET design_config = :design_config,
                    status = :status,
                    error_message = :error_message,
                    updated_at = NOW()
                WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(':design_config', $this->design_config);
        $stmt->bindParam(':status', $this->status);
        $stmt->bindParam(':error_message', $this->error_message);
        $stmt->bindParam(':id', $this->id);
        
        return $stmt->execute();
    }
    
    /**
     * Update status
     */
    public function updateStatus($newStatus, $errorMessage = null) {
        $query = "UPDATE " . $this->table . "
                SET status = :status,
                    error_message = :error_message,
                    updated_at = NOW()
                WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(':status', $newStatus);
        $stmt->bindParam(':error_message', $errorMessage);
        $stmt->bindParam(':id', $this->id);
        
        if ($stmt->execute()) {
            $this->status = $newStatus;
            $this->error_message = $errorMessage;
            return true;
        }
        
        return false;
    }
    
    /**
     * Delete design
     */
    public function delete($hard = false) {
        // Read the design first to get file paths
        if (!$this->readOne()) {
            return false;
        }
        
        // Delete physical files first
        if (file_exists($this->file_path)) {
            unlink($this->file_path);
        }
        if (file_exists($this->preview_path) && $this->preview_path !== $this->file_path) {
            unlink($this->preview_path);
        }
        
        if ($hard) {
            $query = "DELETE FROM " . $this->table . " WHERE id = :id";
        } else {
            // Soft delete
            $query = "UPDATE " . $this->table . " 
                     SET status = 'deleted', updated_at = NOW() 
                     WHERE id = :id";
        }
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $this->id);
        
        return $stmt->execute();
    }
    
    /**
     * Check if design belongs to session
     */
    public function belongsToSession($sessionId) {
        $query = "SELECT id FROM " . $this->table . " 
                  WHERE id = :id AND session_id = :session_id LIMIT 1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $this->id);
        $stmt->bindParam(':session_id', $sessionId);
        $stmt->execute();
        
        return $stmt->rowCount() > 0;
    }
    
    /**
     * Clean up old temporary designs
     */
    public function cleanupOldDesigns($hours = 24) {
        $query = "SELECT * FROM " . $this->table . " 
                  WHERE created_at < DATE_SUB(NOW(), INTERVAL :hours HOUR)
                  AND status IN ('pending', 'processed')";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':hours', $hours);
        $stmt->execute();
        
        $deletedCount = 0;
        
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $this->mapRowToProperties($row);
            
            // Delete files
            if (file_exists($this->file_path)) {
                unlink($this->file_path);
            }
            if (file_exists($this->preview_path) && $this->preview_path !== $this->file_path) {
                unlink($this->preview_path);
            }
            
            // Delete database record
            $deleteQuery = "DELETE FROM " . $this->table . " WHERE id = :id";
            $deleteStmt = $this->conn->prepare($deleteQuery);
            $deleteStmt->bindParam(':id', $this->id);
            if ($deleteStmt->execute()) {
                $deletedCount++;
            }
        }
        
        return $deletedCount;
    }
    
    /**
     * Map database row to object properties
     */
    protected function mapRowToProperties($row) {
        $this->id = $row['id'] ?? null;
        $this->session_id = $row['session_id'] ?? null;
        $this->product_id = $row['product_id'] ?? null;
        $this->original_filename = $row['original_filename'] ?? null;
        $this->stored_filename = $row['stored_filename'] ?? null;
        $this->file_path = $row['file_path'] ?? null;
        $this->preview_path = $row['preview_path'] ?? null;
        $this->file_size = $row['file_size'] ?? null;
        $this->mime_type = $row['mime_type'] ?? null;
        $this->width = $row['width'] ?? null;
        $this->height = $row['height'] ?? null;
        $this->dpi = $row['dpi'] ?? null;
        $this->design_config = $row['design_config'] ?? null;
        $this->status = $row['status'] ?? null;
        $this->error_message = $row['error_message'] ?? null;
        $this->created_at = $row['created_at'] ?? null;
        $this->updated_at = $row['updated_at'] ?? null;
    }
    
    /**
     * Convert object to array
     */
    public function toArray($includeProductName = false) {
        // Use helper function if available, otherwise create simple version
        $fileSizeFormatted = function_exists('formatFileSize') 
            ? formatFileSize($this->file_size) 
            : round($this->file_size / 1024 / 1024, 2) . ' MB';
        
        $data = [
            'id' => intval($this->id),
            'session_id' => $this->session_id,
            'product_id' => intval($this->product_id),
            'original_filename' => $this->original_filename,
            'stored_filename' => $this->stored_filename,
            'file_path' => $this->file_path,
            'preview_path' => $this->preview_path,
            'preview_url' => $this->generatePreviewUrl(),
            'file_size' => intval($this->file_size),
            'file_size_formatted' => $fileSizeFormatted,
            'mime_type' => $this->mime_type,
            'dimensions' => [
                'width' => intval($this->width),
                'height' => intval($this->height),
                'dpi' => intval($this->dpi)
            ],
            'design_config' => json_decode($this->design_config ?? '{}', true),
            'status' => $this->status,
            'error_message' => $this->error_message,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at
        ];
        
        return $data;
    }
    
    /**
     * Generate preview URL
     */
    private function generatePreviewUrl() {
        if (!$this->preview_path) {
            return null;
        }
        
        // Convert file path to URL
        $previewUrl = str_replace(ROOT_PATH, BASE_URL, $this->preview_path);
        
        // Ensure the path uses forward slashes
        $previewUrl = str_replace('\\', '/', $previewUrl);
        
        return $previewUrl;
    }
    
    /**
     * Convert database row to array format
     * 
     * Static helper method to convert a database row directly to
     * the array format without creating a Design instance.
     * This is more efficient when processing multiple rows.
     * 
     * @param array $row Database row (PDO::FETCH_ASSOC format)
     * @return array Design data in array format
     */
    public static function rowToArray($row) {
        $fileSizeFormatted = function_exists('formatFileSize') 
            ? formatFileSize($row['file_size'] ?? 0) 
            : round(($row['file_size'] ?? 0) / 1024 / 1024, 2) . ' MB';
        
        // Generate preview URL
        $previewUrl = null;
        if (!empty($row['preview_path'])) {
            $previewUrl = str_replace(ROOT_PATH, BASE_URL, $row['preview_path']);
            $previewUrl = str_replace('\\', '/', $previewUrl);
        }
        
        return [
            'id' => intval($row['id'] ?? 0),
            'session_id' => $row['session_id'] ?? null,
            'product_id' => intval($row['product_id'] ?? 0),
            'product_name' => $row['product_name'] ?? null,
            'original_filename' => $row['original_filename'] ?? '',
            'stored_filename' => $row['stored_filename'] ?? '',
            'file_path' => $row['file_path'] ?? null,
            'preview_path' => $row['preview_path'] ?? null,
            'preview_url' => $previewUrl,
            'file_size' => intval($row['file_size'] ?? 0),
            'file_size_formatted' => $fileSizeFormatted,
            'mime_type' => $row['mime_type'] ?? null,
            'dimensions' => [
                'width' => intval($row['width'] ?? 0),
                'height' => intval($row['height'] ?? 0),
                'dpi' => intval($row['dpi'] ?? 0)
            ],
            'design_config' => json_decode($row['design_config'] ?? '{}', true),
            'status' => $row['status'] ?? null,
            'error_message' => $row['error_message'] ?? null,
            'created_at' => $row['created_at'] ?? null,
            'updated_at' => $row['updated_at'] ?? null
        ];
    }
    
    /**
     * Validate design data before saving
     */
    public function validate() {
        $errors = [];
        
        if (empty($this->session_id)) {
            $errors[] = 'Session ID is required';
        }
        
        if (empty($this->product_id)) {
            $errors[] = 'Product ID is required';
        }
        
        if (empty($this->original_filename)) {
            $errors[] = 'Original filename is required';
        }
        
        if (empty($this->stored_filename)) {
            $errors[] = 'Stored filename is required';
        }
        
        if (empty($this->file_path)) {
            $errors[] = 'File path is required';
        }
        
        if (empty($this->status)) {
            $this->status = DESIGN_STATUS_PENDING;
        }
        
        return $errors;
    }
}
?>