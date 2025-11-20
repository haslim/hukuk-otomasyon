<?php

require_once 'bootstrap/app.php';

try {
    echo "Checking arbitration tables...\n";
    
    // Check if tables exist
    $tables = Illuminate\Support\Facades\DB::select('SHOW TABLES');
    $tableNames = [];
    foreach ($tables as $table) {
        $tableName = array_values((array)$table)[0];
        $tableNames[] = $tableName;
    }
    
    $requiredTables = [
        'arbitration_applications',
        'application_documents', 
        'application_timeline'
    ];
    
    $missingTables = array_diff($requiredTables, $tableNames);
    
    if (empty($missingTables)) {
        echo "All arbitration tables exist.\n";
        
        // Test a simple query
        $count = Illuminate\Support\Facades\DB::table('arbitration_applications')->count();
        echo "Arbitration applications count: $count\n";
        
    } else {
        echo "Missing tables: " . implode(', ', $missingTables) . "\n";
        
        // Create tables manually
        echo "Creating arbitration tables...\n";
        
        // Create arbitration_applications table
        if (in_array('arbitration_applications', $missingTables)) {
            Illuminate\Support\Facades\DB::statement("
                CREATE TABLE arbitration_applications (
                    id CHAR(36) PRIMARY KEY,
                    application_no VARCHAR(255) UNIQUE,
                    applicant_info JSON,
                    respondent_info JSON,
                    application_type ENUM('ihtiyati', 'ihtiyati_tedbir', 'ticari', 'is_hukuku', 'tuketici', 'icra', 'diger') DEFAULT 'diger',
                    subject_matter TEXT,
                    monetary_value DECIMAL(15,2) NULL,
                    currency VARCHAR(3) DEFAULT 'TRY',
                    application_date DATE,
                    status ENUM('pending', 'accepted', 'rejected', 'in_progress', 'completed', 'cancelled') DEFAULT 'pending',
                    created_by CHAR(36) NULL,
                    mediator_id CHAR(36) NULL,
                    notes TEXT NULL,
                    metadata JSON NULL,
                    created_at TIMESTAMP NULL DEFAULT NULL,
                    updated_at TIMESTAMP NULL DEFAULT NULL,
                    deleted_at TIMESTAMP NULL DEFAULT NULL,
                    INDEX idx_status_application_date (status, application_date),
                    INDEX idx_created_by (created_by),
                    INDEX idx_mediator_id (mediator_id),
                    INDEX idx_application_type (application_type)
                )
            ");
            echo "Created arbitration_applications table\n";
        }
        
        // Create application_documents table
        if (in_array('application_documents', $missingTables)) {
            Illuminate\Support\Facades\DB::statement("
                CREATE TABLE application_documents (
                    id CHAR(36) PRIMARY KEY,
                    application_id CHAR(36),
                    document_type ENUM('basvuru_dilekcesi', 'delil', 'vekaletname', 'kimlik', 'sirket_belgesi', 'vergi_borcu_yoktur', 'adres_kaydi', 'diger') DEFAULT 'diger',
                    title VARCHAR(255),
                    file_path VARCHAR(255),
                    file_size INT,
                    mime_type VARCHAR(255),
                    uploaded_by CHAR(36) NULL,
                    ocr_text TEXT NULL,
                    ai_summary TEXT NULL,
                    is_public BOOLEAN DEFAULT FALSE,
                    metadata JSON NULL,
                    created_at TIMESTAMP NULL DEFAULT NULL,
                    updated_at TIMESTAMP NULL DEFAULT NULL,
                    deleted_at TIMESTAMP NULL DEFAULT NULL,
                    INDEX idx_application_id_document_type (application_id, document_type),
                    INDEX idx_uploaded_by (uploaded_by)
                )
            ");
            echo "Created application_documents table\n";
        }
        
        // Create application_timeline table
        if (in_array('application_timeline', $missingTables)) {
            Illuminate\Support\Facades\DB::statement("
                CREATE TABLE application_timeline (
                    id CHAR(36) PRIMARY KEY,
                    application_id CHAR(36),
                    event_type VARCHAR(255),
                    description TEXT,
                    event_data JSON NULL,
                    user_id CHAR(36) NULL,
                    created_at TIMESTAMP NULL DEFAULT NULL,
                    updated_at TIMESTAMP NULL DEFAULT NULL,
                    INDEX idx_application_id_created_at (application_id, created_at)
                )
            ");
            echo "Created application_timeline table\n";
        }
        
        echo "All arbitration tables created successfully!\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}