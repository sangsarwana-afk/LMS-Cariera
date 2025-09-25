-- Account Management System Updates
-- Add visibility column to existing tables and create unified account management

USE library_management;

-- Add visibility column to staff table
ALTER TABLE staff ADD COLUMN visibility TINYINT(1) NOT NULL DEFAULT 1;
ALTER TABLE staff ADD COLUMN profile_picture VARCHAR(255) DEFAULT NULL;
ALTER TABLE staff ADD COLUMN phone VARCHAR(20) DEFAULT NULL;
ALTER TABLE staff ADD COLUMN address TEXT DEFAULT NULL;
ALTER TABLE staff ADD COLUMN join_date DATE DEFAULT NULL;

-- Add visibility column to members table  
ALTER TABLE members ADD COLUMN visibility TINYINT(1) NOT NULL DEFAULT 1;
ALTER TABLE members ADD COLUMN profile_picture VARCHAR(255) DEFAULT NULL;

-- Update existing data to set join_date for staff
UPDATE staff SET join_date = DATE(created_at) WHERE join_date IS NULL;

-- Create indexes for better performance
CREATE INDEX idx_staff_visibility ON staff(visibility);
CREATE INDEX idx_members_visibility ON members(visibility);

-- Add some sample profile pictures (placeholder paths)
UPDATE staff SET profile_picture = 'uploads/profiles/admin_default.jpg' WHERE username = 'admin';
UPDATE staff SET profile_picture = 'uploads/profiles/librarian_default.jpg' WHERE username = 'librarian';
UPDATE members SET profile_picture = 'uploads/profiles/member_default.jpg' WHERE profile_picture IS NULL;
