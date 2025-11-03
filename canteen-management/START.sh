#!/bin/bash

###############################################################################
# START Script for Canteen Management System
# Yangtze University
#
# This script automatically starts the PHP development server on macOS
###############################################################################

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

echo -e "${BLUE}"
echo "═══════════════════════════════════════════════════════════"
echo "  Yangtze University Canteen Management System"
echo "  Starting Development Server..."
echo "═══════════════════════════════════════════════════════════"
echo -e "${NC}"

# Check if PHP is installed
if ! command -v php &> /dev/null; then
    echo -e "${RED}✗ PHP is not installed!${NC}"
    echo "  Please install PHP 8+ first:"
    echo "  brew install php"
    exit 1
fi

# Display PHP version
PHP_VERSION=$(php -r "echo PHP_VERSION;")
echo -e "${GREEN}✓ PHP Version: $PHP_VERSION${NC}"

# Check if MySQL is running
if command -v mysql &> /dev/null; then
    if pgrep -x "mysqld" > /dev/null; then
        echo -e "${GREEN}✓ MySQL is running${NC}"
    else
        echo -e "${YELLOW}⚠ MySQL is not running${NC}"
        echo "  Starting MySQL..."
        if command -v mysql.server &> /dev/null; then
            sudo mysql.server start
        else
            echo -e "${RED}  Please start MySQL manually${NC}"
        fi
    fi
else
    echo -e "${YELLOW}⚠ MySQL command not found. Make sure MySQL is installed and running.${NC}"
fi

# Check if database exists
echo -e "\n${BLUE}Checking database...${NC}"
DB_EXISTS=$(mysql -u root -e "SHOW DATABASES LIKE 'canteen_management';" 2>/dev/null | grep -c "canteen_management")

if [ "$DB_EXISTS" -eq 0 ]; then
    echo -e "${YELLOW}⚠ Database 'canteen_management' not found${NC}"
    echo "  Would you like to import it now? (y/n)"
    read -r response
    if [[ "$response" =~ ^[Yy]$ ]]; then
        if [ -f "sql/database.sql" ]; then
            echo "  Importing database..."
            mysql -u root -e "CREATE DATABASE IF NOT EXISTS canteen_management CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
            mysql -u root canteen_management < sql/database.sql
            echo -e "${GREEN}✓ Database imported successfully!${NC}"
        else
            echo -e "${RED}✗ sql/database.sql not found!${NC}"
        fi
    fi
else
    echo -e "${GREEN}✓ Database 'canteen_management' exists${NC}"
fi

# Create necessary directories
echo -e "\n${BLUE}Creating directories...${NC}"
mkdir -p tmp/sessions uploads/students logs
chmod -R 777 tmp uploads logs
echo -e "${GREEN}✓ Directories created${NC}"

# Start PHP server
PORT=8000
echo -e "\n${GREEN}═══════════════════════════════════════════════════════════${NC}"
echo -e "${GREEN}  Starting PHP Development Server on port $PORT${NC}"
echo -e "${GREEN}═══════════════════════════════════════════════════════════${NC}"
echo ""
echo -e "  ${BLUE}URL:${NC}      http://localhost:$PORT"
echo -e "  ${BLUE}Login:${NC}    admin / admin123"
echo -e "  ${BLUE}Stop:${NC}     Press ${YELLOW}Ctrl+C${NC}"
echo ""
echo -e "${GREEN}═══════════════════════════════════════════════════════════${NC}"
echo ""

# Start server with router
php -S localhost:$PORT router.php
