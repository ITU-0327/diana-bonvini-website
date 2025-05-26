#!/bin/bash

# Setup Upload Permissions Script
# Run this on your production server to set up proper upload directories

echo "Setting up upload directories for file uploads..."

# Get the current directory (should be your app root)
APP_ROOT=$(pwd)
WEBROOT="$APP_ROOT/webroot"

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

echo -e "${YELLOW}App root: $APP_ROOT${NC}"
echo -e "${YELLOW}Webroot: $WEBROOT${NC}"

# Function to create directory with proper permissions
create_upload_dir() {
    local dir_path=$1
    local relative_path=$2
    
    echo -e "\n${YELLOW}Setting up: $relative_path${NC}"
    
    # Create directory if it doesn't exist
    if [ ! -d "$dir_path" ]; then
        echo "Creating directory: $dir_path"
        mkdir -p "$dir_path"
        if [ $? -eq 0 ]; then
            echo -e "${GREEN}✓ Directory created successfully${NC}"
        else
            echo -e "${RED}✗ Failed to create directory${NC}"
            return 1
        fi
    else
        echo -e "${GREEN}✓ Directory already exists${NC}"
    fi
    
    # Set ownership to web server user (try common web server users)
    WEB_USERS=("www-data" "apache" "nginx" "httpd")
    
    for user in "${WEB_USERS[@]}"; do
        if id "$user" &>/dev/null; then
            echo "Setting ownership to $user:$user"
            sudo chown -R $user:$user "$dir_path"
            if [ $? -eq 0 ]; then
                echo -e "${GREEN}✓ Ownership set to $user${NC}"
                break
            fi
        fi
    done
    
    # Set permissions
    echo "Setting permissions..."
    chmod 755 "$dir_path"
    chmod -R 644 "$dir_path"/* 2>/dev/null || true
    
    # Make sure the directory itself is writable
    chmod 775 "$dir_path"
    
    if [ -w "$dir_path" ]; then
        echo -e "${GREEN}✓ Directory is writable${NC}"
    else
        echo -e "${RED}✗ Directory is not writable${NC}"
        return 1
    fi
    
    return 0
}

# Create upload directories
echo -e "\n${YELLOW}=== Setting up upload directories ===${NC}"

# Primary upload directory
create_upload_dir "$WEBROOT/uploads/documents" "webroot/uploads/documents"

# Alternative upload directory
create_upload_dir "$WEBROOT/files/documents" "webroot/files/documents"

# Temp upload directory
create_upload_dir "/tmp/uploads/documents" "tmp/uploads/documents"

# Set up .htaccess for security (prevent direct PHP execution in uploads)
echo -e "\n${YELLOW}=== Setting up security ===${NC}"

HTACCESS_CONTENT="# Prevent PHP execution in uploads directory
<Files *.php>
    Order Deny,Allow
    Deny from all
</Files>

# Prevent access to sensitive files
<FilesMatch \"\\.(htaccess|htpasswd|ini|log|sh|inc|bak)$\">
    Order Deny,Allow
    Deny from all
</FilesMatch>

# Allow common document types
<FilesMatch \"\\.(pdf|doc|docx|txt|jpg|jpeg|png|gif)$\">
    Order Allow,Deny
    Allow from all
</FilesMatch>"

for dir in "$WEBROOT/uploads" "$WEBROOT/files"; do
    if [ -d "$dir" ]; then
        echo "Creating .htaccess in $dir"
        echo "$HTACCESS_CONTENT" > "$dir/.htaccess"
        chmod 644 "$dir/.htaccess"
        echo -e "${GREEN}✓ Security .htaccess created${NC}"
    fi
done

# Final summary
echo -e "\n${YELLOW}=== Summary ===${NC}"
echo "Upload directories have been set up. Please check the following:"
echo "1. webroot/uploads/documents - Primary upload location"
echo "2. webroot/files/documents - Alternative upload location" 
echo "3. /tmp/uploads/documents - Fallback location"
echo ""
echo "If you're still having permission issues, you may need to:"
echo "1. Run this script with sudo"
echo "2. Check your web server configuration"
echo "3. Ensure your hosting provider allows file uploads"
echo "4. Check PHP upload settings (upload_max_filesize, post_max_size)"

# Check PHP settings
echo -e "\n${YELLOW}=== PHP Upload Settings ===${NC}"
if command -v php &> /dev/null; then
    echo "Current PHP upload settings:"
    php -r "
    echo 'upload_max_filesize: ' . ini_get('upload_max_filesize') . PHP_EOL;
    echo 'post_max_size: ' . ini_get('post_max_size') . PHP_EOL;
    echo 'max_file_uploads: ' . ini_get('max_file_uploads') . PHP_EOL;
    echo 'file_uploads: ' . (ini_get('file_uploads') ? 'On' : 'Off') . PHP_EOL;
    echo 'upload_tmp_dir: ' . ini_get('upload_tmp_dir') . PHP_EOL;
    "
else
    echo "PHP command not found. Please check PHP settings manually."
fi

echo -e "\n${GREEN}Setup complete!${NC}" 