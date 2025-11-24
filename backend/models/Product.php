<?php
/**
 * Product Model
 * Handles all product-related database operations
 */

class Product {
    private $conn;
    private $table = 'products';
    
    // Product properties
    public $id;
    public $shopify_product_id;
    public $shopify_variant_id;
    public $name;
    public $description;
    public $base_price;
    public $print_area_width;
    public $print_area_height;
    public $min_dpi;
    public $max_file_size;
    public $allowed_formats;
    public $product_type;
    public $is_active;
    public $created_at;
    public $updated_at;
    
    public function __construct($db) {
        $this->conn = $db;
    }
    
    /**
     * Get database connection
     * 
     * Returns the database connection object. This method allows
     * external code to access the connection when needed (e.g., 
     * for creating new model instances with the same connection).
     * 
     * @return PDO Database connection object
     */
    public function getConnection() {
        return $this->conn;
    }
    
    /**
     * Get all active products
     */
    public function read($activeOnly = true) {
        $query = "SELECT * FROM " . $this->table;
        
        if ($activeOnly) {
            $query .= " WHERE is_active = 1";
        }
        
        $query .= " ORDER BY created_at DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        return $stmt;
    }
    
    /**
     * Get single product by ID
     */
    public function readOne() {
        $query = "SELECT * FROM " . $this->table . " WHERE id = :id LIMIT 1";
        
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
     * Get product by Shopify product ID
     */
    public function readByShopifyId() {
        $query = "SELECT * FROM " . $this->table . " WHERE shopify_product_id = :shopify_id LIMIT 1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':shopify_id', $this->shopify_product_id);
        $stmt->execute();
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($row) {
            $this->mapRowToProperties($row);
            return true;
        }
        
        return false;
    }
    
    /**
     * Create new product
     */
    public function create() {
        $query = "INSERT INTO " . $this->table . "
                SET shopify_product_id = :shopify_product_id,
                    shopify_variant_id = :shopify_variant_id,
                    name = :name,
                    description = :description,
                    base_price = :base_price,
                    print_area_width = :print_area_width,
                    print_area_height = :print_area_height,
                    min_dpi = :min_dpi,
                    max_file_size = :max_file_size,
                    allowed_formats = :allowed_formats,
                    product_type = :product_type,
                    is_active = :is_active";
        
        $stmt = $this->conn->prepare($query);
        
        // Bind values
        $stmt->bindParam(':shopify_product_id', $this->shopify_product_id);
        $stmt->bindParam(':shopify_variant_id', $this->shopify_variant_id);
        $stmt->bindParam(':name', $this->name);
        $stmt->bindParam(':description', $this->description);
        $stmt->bindParam(':base_price', $this->base_price);
        $stmt->bindParam(':print_area_width', $this->print_area_width);
        $stmt->bindParam(':print_area_height', $this->print_area_height);
        $stmt->bindParam(':min_dpi', $this->min_dpi);
        $stmt->bindParam(':max_file_size', $this->max_file_size);
        $stmt->bindParam(':allowed_formats', $this->allowed_formats);
        $stmt->bindParam(':product_type', $this->product_type);
        $stmt->bindParam(':is_active', $this->is_active);
        
        if ($stmt->execute()) {
            $this->id = $this->conn->lastInsertId();
            return true;
        }
        
        return false;
    }
    
    /**
     * Update product
     */
    public function update() {
        $query = "UPDATE " . $this->table . "
                SET name = :name,
                    description = :description,
                    base_price = :base_price,
                    print_area_width = :print_area_width,
                    print_area_height = :print_area_height,
                    min_dpi = :min_dpi,
                    max_file_size = :max_file_size,
                    allowed_formats = :allowed_formats,
                    product_type = :product_type,
                    is_active = :is_active
                WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        
        // Bind values
        $stmt->bindParam(':name', $this->name);
        $stmt->bindParam(':description', $this->description);
        $stmt->bindParam(':base_price', $this->base_price);
        $stmt->bindParam(':print_area_width', $this->print_area_width);
        $stmt->bindParam(':print_area_height', $this->print_area_height);
        $stmt->bindParam(':min_dpi', $this->min_dpi);
        $stmt->bindParam(':max_file_size', $this->max_file_size);
        $stmt->bindParam(':allowed_formats', $this->allowed_formats);
        $stmt->bindParam(':product_type', $this->product_type);
        $stmt->bindParam(':is_active', $this->is_active);
        $stmt->bindParam(':id', $this->id);
        
        return $stmt->execute();
    }
    
    /**
     * Delete product (soft delete by setting is_active = 0)
     */
    public function delete($hard = false) {
        if ($hard) {
            $query = "DELETE FROM " . $this->table . " WHERE id = :id";
        } else {
            $query = "UPDATE " . $this->table . " SET is_active = 0 WHERE id = :id";
        }
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $this->id);
        
        return $stmt->execute();
    }
    
    /**
     * Check if product exists
     */
    public function exists() {
        $query = "SELECT id FROM " . $this->table . " WHERE id = :id LIMIT 1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $this->id);
        $stmt->execute();
        
        return $stmt->rowCount() > 0;
    }
    
    /**
     * Get products by type
     */
    public function getByType($type) {
        $query = "SELECT * FROM " . $this->table . " 
                  WHERE product_type = :type AND is_active = 1 
                  ORDER BY name ASC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':type', $type);
        $stmt->execute();
        
        return $stmt;
    }
    
    /**
     * Map database row to object properties
     */
    private function mapRowToProperties($row) {
        $this->id = $row['id'];
        $this->shopify_product_id = $row['shopify_product_id'];
        $this->shopify_variant_id = $row['shopify_variant_id'];
        $this->name = $row['name'];
        $this->description = $row['description'];
        $this->base_price = $row['base_price'];
        $this->print_area_width = $row['print_area_width'];
        $this->print_area_height = $row['print_area_height'];
        $this->min_dpi = $row['min_dpi'];
        $this->max_file_size = $row['max_file_size'];
        $this->allowed_formats = $row['allowed_formats'];
        $this->product_type = $row['product_type'];
        $this->is_active = $row['is_active'];
        $this->created_at = $row['created_at'];
        $this->updated_at = $row['updated_at'];
    }
    
    /**
     * Convert object to array
     */
    public function toArray() {
        return [
            'id' => $this->id,
            'shopify_product_id' => $this->shopify_product_id,
            'shopify_variant_id' => $this->shopify_variant_id,
            'name' => $this->name,
            'description' => $this->description,
            'base_price' => floatval($this->base_price),
            'print_area_width' => intval($this->print_area_width),
            'print_area_height' => intval($this->print_area_height),
            'min_dpi' => intval($this->min_dpi),
            'max_file_size' => intval($this->max_file_size),
            'allowed_formats' => json_decode($this->allowed_formats ?? '[]'),
            'product_type' => $this->product_type,
            'is_active' => boolval($this->is_active),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at
        ];
    }
    
    /**
     * Convert database row to array format
     * 
     * Static helper method to convert a database row directly to
     * the array format without creating a Product instance.
     * This is more efficient when processing multiple rows.
     * 
     * @param array $row Database row (PDO::FETCH_ASSOC format)
     * @return array Product data in array format
     */
    public static function rowToArray($row) {
        return [
            'id' => intval($row['id'] ?? 0),
            'shopify_product_id' => $row['shopify_product_id'] ?? '',
            'shopify_variant_id' => $row['shopify_variant_id'] ?? '',
            'name' => $row['name'] ?? '',
            'description' => $row['description'] ?? '',
            'base_price' => floatval($row['base_price'] ?? 0),
            'print_area_width' => intval($row['print_area_width'] ?? 0),
            'print_area_height' => intval($row['print_area_height'] ?? 0),
            'min_dpi' => intval($row['min_dpi'] ?? 0),
            'max_file_size' => intval($row['max_file_size'] ?? 0),
            'allowed_formats' => json_decode($row['allowed_formats'] ?? '[]', true),
            'product_type' => $row['product_type'] ?? '',
            'is_active' => boolval($row['is_active'] ?? false),
            'created_at' => $row['created_at'] ?? null,
            'updated_at' => $row['updated_at'] ?? null
        ];
    }
}
?>