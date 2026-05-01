# ChaleDash+

A peer-to-peer lending platform that connects lenders and borrowers in a secure, transparent environment.

## Quick Start

### Prerequisites
- [Docker](https://www.docker.com/get-started) installed on your machine
- Git (to clone the repository)

### Running the Application

1. **Clone the repository**
   ```bash
   git clone <your-repo-url>
   cd charleedash-plus
   ```

2. **Start the application**
   ```bash
   docker-compose up --build
   ```

3. **Access the application**
   - **Frontend**: http://localhost:8090
   - **Backend API**: http://localhost:8091
   - **Database Admin**: http://localhost:8092
     - Username: `root`
     - Password: `Chacha@1583`

## How to Use

### For Borrowers
1. **Register** with your phone number
2. **Verify** your account with the OTP code
3. **Browse** available lending vaults
4. **Request** loans from lenders
5. **Repay** loans when approved

### For Lenders
1. **Register** and verify your account
2. **Create** lending vaults with your terms
3. **Wait** for borrower requests
4. **Earn** interest on approved loans

### For Admins
1. **Login** with admin credentials:
   - Username: `admin`
   - Password: `Admin@1234`
2. **Review** loan requests
3. **Approve** or reject applications
4. **Monitor** platform activity


### Tech Stack
- **Frontend**: React.js with Tailwind CSS
- **Backend**: PHP with PDO
- **Database**: MySQL 8.0
- **Containerization**: Docker & Docker Compose
- **Web Server**: Nginx (Frontend) + Apache (Backend)

## Database Schema

The system uses 5 main tables:

- **`users`** - User accounts and authentication
- **`vaults`** - Lending offers created by lenders
- **`loan_requests`** - Borrower requests for specific vaults
- **`active_contracts`** - Approved loans being repaid
- **`transactions`** - Payment and loan history

## Development

### Project Structure
```
charleedash-plus/
├── src/                    # React frontend source
├── api/                    # PHP API endpoints
├── *.php                   # Backend PHP files
├── charleedash_dump.sql    # Database schema
├── docker-compose.yml      # Docker configuration
└── README.md              # This file
```

### Key Features
- Phone-based registration with OTP
- JWT-like token authentication
- Vault-based lending system
- Admin approval workflow
- Transaction tracking
- Real-time loan status updates

## Troubleshooting

### Common Issues

**Port conflicts:**
```bash
# Stop the application
docker-compose down

# Check what's using the ports
netstat -an | grep :8090
netstat -an | grep :8091
netstat -an | grep :8092
```

## API Endpoints

### Authentication
- `POST /register.php` - Register new user
- `POST /verify.php` - Verify OTP code
- `POST /login.php` - User login
- `POST /logout.php` - User logout

### Lending
- `GET /available_loans.php` - Get available vaults
- `POST /Lend.php` - Create lending vault
- `POST /loan_request.php` - Request loan from vault
- `POST /repayment.php` - Make loan repayment

### Admin
- `POST /admin_login.php` - Admin login
- `GET /admin_dashboard.php` - Admin overview
- `POST /admin_loan_action.php` - Approve/reject loans
