# Account Management System Setup Guide

## 📋 Overview
The account management system has been successfully implemented for your LMS with comprehensive features for managing members, librarians, and administrators.

## 🗄️ Database Setup

1. **Apply Database Updates**
   Run the SQL script to add necessary columns and indexes:
   ```sql
   -- Execute this in phpMyAdmin or MySQL command line
   SOURCE database/account_management_update.sql;
   ```

   Or manually run these commands:
   ```sql
   USE library_management;
   
   -- Add visibility and profile picture columns to staff table
   ALTER TABLE staff ADD COLUMN visibility TINYINT(1) NOT NULL DEFAULT 1;
   ALTER TABLE staff ADD COLUMN profile_picture VARCHAR(255) DEFAULT NULL;
   ALTER TABLE staff ADD COLUMN phone VARCHAR(20) DEFAULT NULL;
   ALTER TABLE staff ADD COLUMN address TEXT DEFAULT NULL;
   ALTER TABLE staff ADD COLUMN join_date DATE DEFAULT NULL;
   
   -- Add visibility and profile picture columns to members table  
   ALTER TABLE members ADD COLUMN visibility TINYINT(1) NOT NULL DEFAULT 1;
   ALTER TABLE members ADD COLUMN profile_picture VARCHAR(255) DEFAULT NULL;
   
   -- Update existing data
   UPDATE staff SET join_date = DATE(created_at) WHERE join_date IS NULL;
   
   -- Create indexes
   CREATE INDEX idx_staff_visibility ON staff(visibility);
   CREATE INDEX idx_members_visibility ON members(visibility);
   ```

## 🚀 Features Implemented

### ✅ Account List View
- **Grid Layout**: Clean card-based interface showing all accounts
- **Profile Pictures**: Default avatar system with fallback images
- **Permission Badges**: Color-coded badges (Admin: Red, Librarian: Orange, Member: Green)
- **Search & Filter**: Real-time search by name/email and filter by permission level

### ✅ Add Account (Admin Only)
- **Unified Form**: Single form for all account types
- **Smart Fields**: Dynamic form fields based on permission level
- **Auto Generation**: Automatic username generation for staff accounts
- **Default Passwords**: Secure default password system

### ✅ Account Detail View
- **Complete Profile**: Full personal information display
- **Loan History**: Shows borrowing history for members
- **Permission Display**: Clear role and permission information
- **Responsive Design**: Mobile-friendly modal layout

### ✅ Edit Account (Admin Only)
- **Full Editing**: Modify all account details
- **Permission Changes**: Change user roles and permissions
- **Password Reset**: Change passwords for staff accounts
- **Data Validation**: Client and server-side validation

### ✅ Soft Delete (Admin Only)
- **Confirmation Dialog**: Safety confirmation before deletion
- **Soft Delete**: Sets visibility to 0 instead of hard delete
- **Data Preservation**: Maintains referential integrity
- **Reversible**: Can be restored by setting visibility back to 1

## 🔐 Permission System

### **Admin**
- Full access to all features
- Can add, edit, delete any account
- Can change user permissions
- Can reset passwords

### **Librarian**
- Can view all accounts
- Cannot modify accounts
- Can manage books and loans

### **Member**
- Cannot access account management
- Limited to borrowing interface

## 📁 File Structure

```
LMS/
├── public/
│   └── members.php              # Main account management interface
├── src/
│   └── api/
│       └── accounts.php         # API endpoints for CRUD operations
├── database/
│   └── account_management_update.sql  # Database schema updates
├── assets/
│   ├── css/
│   │   └── style.css           # Updated styles
│   └── images/
│       └── default-avatar.jpg   # Default profile picture
└── uploads/
    └── profiles/               # Directory for user uploads
```

## 🎯 Usage Instructions

### **Accessing Account Management**
1. Login as admin user
2. Click "Kelola Anggota" from dashboard or navigation
3. View all accounts in grid layout

### **Adding New Accounts**
1. Click "Tambah Akun" button (admin only)
2. Fill required fields (Name, Email, Permission Level)
3. For staff accounts, optionally set username/password
4. Submit form to create account

### **Viewing Account Details**
1. Click any account card in the grid
2. View complete profile information
3. See loan history (for members)
4. Use Edit/Delete buttons (admin only)

### **Editing Accounts**
1. Open account detail modal
2. Click "Edit" button (admin only)
3. Modify any field as needed
4. Optionally change password (staff only)
5. Save changes

### **Deleting Accounts**
1. Open account detail modal
2. Click "Hapus" (Delete) button
3. Confirm deletion in popup
4. Account is hidden from system (soft delete)

## 🔧 Technical Details

### **API Endpoints**
- `GET /src/api/accounts.php?action=all` - Get all visible accounts
- `GET /src/api/accounts.php?action=detail&type=X&id=Y` - Get account details
- `GET /src/api/accounts.php?action=loan_history&type=X&id=Y` - Get loan history
- `POST /src/api/accounts.php?action=create` - Create new account
- `PUT /src/api/accounts.php?action=update` - Update account
- `PUT /src/api/accounts.php?action=change_password` - Change password
- `DELETE /src/api/accounts.php?action=soft_delete&type=X&id=Y` - Soft delete

### **Security Features**
- Session-based authentication
- Role-based access control
- SQL injection prevention (prepared statements)
- XSS protection (input sanitization)
- CSRF protection considerations

### **Responsive Design**
- Mobile-friendly interface
- Collapsible navigation on small screens
- Adaptive grid layouts
- Touch-friendly buttons and modals

## 🐛 Troubleshooting

### **Common Issues**

1. **"Error loading accounts"**
   - Check database connection in `config/database.php`
   - Ensure database schema is updated
   - Check browser console for JavaScript errors

2. **Images not loading**
   - Verify `assets/images/` directory exists
   - Check file permissions on upload directories
   - Ensure default avatar file exists

3. **Permission denied errors**
   - Verify user session and role
   - Check if user has admin privileges
   - Clear browser cache and cookies

4. **Modal not opening**
   - Check for JavaScript errors in console
   - Verify jQuery/vanilla JS compatibility
   - Ensure CSS files are loaded correctly

## 📱 Browser Compatibility

- Chrome 70+
- Firefox 65+
- Safari 12+
- Edge 79+
- Mobile browsers supported

## 🔄 Future Enhancements

Potential improvements you could add:
- Profile picture upload functionality
- Bulk operations (delete multiple accounts)
- Export account data to CSV/Excel
- Email notifications for account changes
- Account activity logs
- Password strength requirements
- Two-factor authentication

## 📞 Support

If you encounter any issues:
1. Check the browser console for JavaScript errors
2. Verify database connection and schema
3. Ensure proper file permissions
4. Review PHP error logs

The system is now ready for production use! 🎉
