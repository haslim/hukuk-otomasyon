-- Create arbitration_applications table
CREATE TABLE IF NOT EXISTS arbitration_applications (
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
);

-- Create application_documents table
CREATE TABLE IF NOT EXISTS application_documents (
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
);

-- Create application_timeline table
CREATE TABLE IF NOT EXISTS application_timeline (
    id CHAR(36) PRIMARY KEY,
    application_id CHAR(36),
    event_type VARCHAR(255),
    description TEXT,
    event_data JSON NULL,
    user_id CHAR(36) NULL,
    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL,
    INDEX idx_application_id_created_at (application_id, created_at)
);