-- Check if the database already exists before creation (for safety)
CREATE DATABASE IF NOT EXISTS devops_snippet_db;
USE devops_snippet_db;

-- Create the table to store code snippets
CREATE TABLE IF NOT EXISTS snippets (
    -- Unique identifier for the snippet (used in the URL)
    id VARCHAR(8) PRIMARY KEY,
    
    -- The actual code content (TEXT is large enough for code)
    code_content TEXT NOT NULL,
    
    -- The language type for syntax highlighting
    lang_type VARCHAR(50) NOT NULL,
    
    -- Timestamp when the snippet was created
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Optional: Add an index for faster retrieval by ID (though ID is already primary key)
-- CREATE INDEX idx_id ON snippets (id);

-- Insert a default snippet for demonstration
INSERT INTO snippets (id, code_content, lang_type) VALUES 
('a0b1c2d3', 'function helloDevOps() {\n  console.log("Container networking is working!");\n}', 'javascript');
