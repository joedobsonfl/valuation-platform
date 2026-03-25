CREATE TABLE IF NOT EXISTS diligence_items (
    id BIGSERIAL PRIMARY KEY,
    company_id BIGINT NOT NULL REFERENCES companies(id) ON DELETE CASCADE,
    category VARCHAR(100) NOT NULL,
    subcategory VARCHAR(100),
    title VARCHAR(255) NOT NULL,
    description TEXT,
    source_type VARCHAR(50),
    priority VARCHAR(20) DEFAULT 'medium',
    status VARCHAR(20) DEFAULT 'open',
    owner VARCHAR(255),
    due_date DATE,
    created_at TIMESTAMP NOT NULL DEFAULT NOW(),
    updated_at TIMESTAMP NOT NULL DEFAULT NOW()
);

CREATE INDEX IF NOT EXISTS idx_diligence_items_company_id ON diligence_items(company_id);
CREATE INDEX IF NOT EXISTS idx_diligence_items_category ON diligence_items(category);
CREATE INDEX IF NOT EXISTS idx_diligence_items_status ON diligence_items(status);