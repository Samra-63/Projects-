# PayRoll Management System

A comprehensive Laravel-based PayRoll Management System designed for Pakistani businesses with full employee management, salary processing, attendance tracking, and reporting capabilities.

##  Features

###  Employee Management
- **Employee Registration & Profiles**: Complete employee information management
- **Department Management**: Organize employees by departments with manager assignments
- **Employee Search & Filtering**: Quick search and filter capabilities
- **Profile Management**: Employee profile updates and settings

###  Salary Management
- **Salary Structure**: Base salary, allowances, deductions, and overtime calculations
- **Salary Processing**: Monthly salary generation with PKR currency support
- **Payment Status Tracking**: Track paid, pending, and overdue payments
- **Salary Reports**: Detailed salary reports and analytics
- **Real-time Calculations**: Live salary calculation with overtime and deductions

###  Attendance Tracking
- **Daily Attendance**: Record employee check-ins and check-outs
- **Attendance Reports**: Generate attendance reports by date range
- **Leave Management**: Request and approve leave applications
- **Attendance Analytics**: View attendance patterns and statistics

###  Reporting & Analytics
- **Comprehensive Reports**: Salary, attendance, and leave reports
- **Export Functionality**: Export reports to CSV format
- **Dashboard Analytics**: Real-time dashboard with key metrics
- **Department-wise Reports**: Filter reports by departments

###  User Management & Security
- **User Authentication**: Secure login and registration system
- **Role-based Access**: Different access levels for administrators and employees
- **Profile Settings**: User profile management and password changes
- **Session Management**: Secure session handling

###  User Interface
- **Modern UI**: Clean and responsive Bootstrap-based interface
- **User Dropdown Menu**: Quick access to profile, settings, and logout
- **Notifications**: Real-time notification system
- **Mobile Responsive**: Works seamlessly on all devices

##  Technology Stack

- **Backend**: Laravel 10.x (PHP 8.1+)
- **Frontend**: Blade Templates with Bootstrap 5
- **Database**: MySQL/PostgreSQL
- **Authentication**: Laravel Sanctum
- **Currency**: Pakistani Rupees (PKR) - ₨

##  Prerequisites

Before running this application, make sure you have:

- PHP >= 8.1
- Composer
- MySQL/PostgreSQL
- Node.js & NPM (for asset compilation)
- Web server (Apache/Nginx) or PHP built-in server

##  Installation

1. **Clone the repository**
   ```bash
   git clone <repository-url>
   cd PayRoll
   ```

2. **Install PHP dependencies**
   ```bash
   composer install
   ```

3. **Install Node.js dependencies**
   ```bash
   npm install
   ```

4. **Environment setup**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

5. **Configure database**
   Edit `.env` file and set your database credentials:
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=payroll_db
   DB_USERNAME=your_username
   DB_PASSWORD=your_password
   ```

6. **Run migrations and seeders**
   ```bash
   php artisan migrate
   php artisan db:seed
   ```

7. **Compile assets**
   ```bash
   npm run dev
   ```

8. **Start the application**
   ```bash
   php artisan serve
   ```

The application will be available at `http://localhost:8000`

##  Default Login Credentials

After running the seeders, you can login with:

- **Email**: admin@payroll.com
- **Password**: password

## 📁 Project Structure

```
PayRoll/
├── app/
│   ├── Http/Controllers/     # Application controllers
│   ├── Models/              # Eloquent models
│   └── Providers/           # Service providers
├── database/
│   ├── migrations/          # Database migrations
│   └── seeders/            # Database seeders
├── resources/
│   └── views/              # Blade templates
├── routes/                 # Application routes
└── public/                # Public assets
```

##  Key Features Explained

### Dashboard
- **Overview Statistics**: Total employees, departments, active leave requests
- **Recent Activities**: Latest employee activities, salary updates, attendance records
- **Quick Actions**: Quick access to common functions
- **Real-time Updates**: Live data updates

### Employee Management
- **Employee List**: View all employees with search and filter options
- **Add Employee**: Complete employee registration form
- **Edit Employee**: Update employee information and salary details
- **Employee Profile**: Detailed employee view with all information

### Salary Processing
- **Salary Calculation**: Automatic calculation of base salary, overtime, and deductions
- **Payment Tracking**: Track payment status (Paid, Pending, Overdue)
- **Salary Reports**: Generate detailed salary reports
- **Export Functionality**: Export salary data to CSV

### Attendance System
- **Daily Attendance**: Record employee attendance
- **Leave Requests**: Employee leave application system
- **Attendance Reports**: Generate attendance reports
- **Leave Approval**: Manager approval for leave requests

### Reporting System
- **Multiple Report Types**: Salary, attendance, and leave reports
- **Date Range Filtering**: Filter reports by date ranges
- **Department Filtering**: Filter reports by departments
- **Export Options**: Export reports in CSV format

##  Configuration

### Currency Settings
The system is configured for Pakistani Rupees (PKR) with the symbol ₨. All monetary values are displayed in PKR format.

### Database Configuration
The system uses Laravel's Eloquent ORM with the following main models:
- `User`: Authentication and user management
- `Employee`: Employee information and profiles
- `Department`: Department management
- `Salary`: Salary records and calculations
- `Attendance`: Attendance tracking
- `LeaveRequest`: Leave management

## Database Schema

### Key Tables
- `users`: User authentication and profiles
- `employees`: Employee information
- `departments`: Department information
- `salaries`: Salary records
- `attendance`: Attendance records
- `leave_requests`: Leave applications

## Usage Guide

### For Administrators
1. **Dashboard**: Monitor overall system statistics
2. **Employee Management**: Add, edit, and manage employees
3. **Salary Processing**: Process monthly salaries
4. **Reports**: Generate and export various reports
5. **System Settings**: Configure system parameters

### For Employees
1. **Profile Management**: Update personal information
2. **Attendance**: Mark daily attendance
3. **Leave Requests**: Apply for leave
4. **Salary View**: View salary information
5. **Settings**: Change password and preferences

##  Security Features

- **Authentication**: Secure login system
- **Authorization**: Role-based access control
- **CSRF Protection**: Cross-site request forgery protection
- **Input Validation**: Comprehensive input validation
- **SQL Injection Protection**: Eloquent ORM protection

##  Responsive Design

The application is fully responsive and works on:
- Desktop computers
- Tablets
- Mobile phones
- All modern web browsers

##  Maintenance

### Regular Tasks
- **Database Backups**: Regular database backups
- **Log Monitoring**: Monitor application logs
- **Updates**: Keep Laravel and dependencies updated
- **Performance**: Monitor application performance

### Troubleshooting
- Check Laravel logs in `storage/logs/`
- Verify database connections
- Ensure proper file permissions
- Check server requirements

##  Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Test thoroughly
5. Submit a pull request

##  License

This project is licensed under the MIT License.

##  Support

For support and questions:
- Check the documentation
- Review the code comments
- Create an issue in the repository

##  Version History

- **v1.0.0**: Initial release with core payroll features
- **v1.1.0**: Added reporting and export functionality
- **v1.2.0**: Enhanced UI and user management features

---

**Built with using Laravel Framework**
