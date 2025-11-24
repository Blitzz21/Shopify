<?php
/**
 * Print File Generator
 * 
 * Creates print-ready files for Polaris integration
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../utils/helpers.php';

class PrintFileGenerator
{
    /** @var PDO */
    private $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    /**
     * Generate a print-ready file for a given print job.
     */
    public function generateForJob(int $jobId): array
    {
        // 1) Load job + design + product info
        $query = "
            SELECT 
                pj.id AS job_id,
                pj.print_file_path,
                pj.status,
                pj.notes,
                oi.id AS order_item_id,
                d.id AS design_id,
                d.file_path AS design_file_path,
                d.width AS design_width,
                d.height AS design_height,
                d.dpi AS design_dpi,
                d.design_config,
                p.id AS product_id,
                p.name AS product_name,
                p.print_area_width,
                p.print_area_height
            FROM print_jobs pj
            INNER JOIN order_items oi ON pj.order_item_id = oi.id
            INNER JOIN designs d ON pj.design_id = d.id
            INNER JOIN products p ON oi.product_id = p.id
            WHERE pj.id = :job_id
            LIMIT 1
        ";

        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':job_id', $jobId, PDO::PARAM_INT);
        $stmt->execute();

        $job = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$job) {
            throw new Exception("Print job not found (ID: {$jobId})");
        }

        $sourceFile = $job['design_file_path'];

        // 🔧 FIXED: Handle relative paths properly
        if (empty($sourceFile)) {
            throw new Exception("Design file path is empty for job {$jobId}");
        }

        // Convert relative path to absolute path
        if (strpos($sourceFile, '/') === 0) {
            // Path starts with / - it's relative to root
            $absolutePath = ROOT_PATH . ltrim($sourceFile, '/');
        } else {
            $absolutePath = $sourceFile;
        }

        // Normalize path separators for Windows
        $absolutePath = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $absolutePath);

        if (!file_exists($absolutePath)) {
            throw new Exception("Design file not found for job {$jobId}. Looked for: " . $absolutePath);
        }

        $sourceFile = $absolutePath;

        // 2) Ensure destination directory exists
        if (!file_exists(PRINT_READY_PATH)) {
            if (!mkdir(PRINT_READY_PATH, 0755, true)) {
                throw new Exception("Failed to create print-ready directory: " . PRINT_READY_PATH);
            }
        }

        // 3) Build destination filename
        $ext = pathinfo($sourceFile, PATHINFO_EXTENSION);
        if (!$ext) {
            $ext = 'png'; // fallback
        }

        $destFilename = sprintf(
            'job_%d_design_%d_%s.%s',
            $job['job_id'],
            $job['design_id'],
            date('Ymd_His'),
            $ext
        );

        $destPath = PRINT_READY_PATH . $destFilename;

        // 4) Copy original design as "print-ready" file
        if (!copy($sourceFile, $destPath)) {
            throw new Exception("Failed to copy design file to print-ready location");
        }

        // 5) Update print_jobs.print_file_path
        $update = "
            UPDATE print_jobs
            SET print_file_path = :print_file_path,
                updated_at = CURRENT_TIMESTAMP
            WHERE id = :job_id
        ";

        $updateStmt = $this->db->prepare($update);
        $updateStmt->bindParam(':print_file_path', $destPath);
        $updateStmt->bindParam(':job_id', $jobId, PDO::PARAM_INT);
        $updateStmt->execute();

        // 6) Re-load updated job
        $reload = $this->db->prepare("SELECT * FROM print_jobs WHERE id = :job_id LIMIT 1");
        $reload->bindParam(':job_id', $jobId, PDO::PARAM_INT);
        $reload->execute();
        $updatedJob = $reload->fetch(PDO::FETCH_ASSOC);

        if (!$updatedJob) {
            throw new Exception("Failed to reload updated print job");
        }

        // 7) Add URL for frontend access
        $updatedJob['print_file_url'] = $this->buildPublicUrl($updatedJob['print_file_path']);

        return $updatedJob;
    }

    /**
     * Convert filesystem path to public URL
     */
    private function buildPublicUrl(?string $path): ?string
    {
        if (!$path) {
            return null;
        }

        // Convert ROOT_PATH → BASE_URL
        $url = str_replace(ROOT_PATH, BASE_URL, $path);
        $url = str_replace('\\', '/', $url);

        return $url;
    }
}
?>