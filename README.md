# CharleeDash+ 

**Empowering the Campus Economy.**  
CharleeDash+ is a secure, anonymous peer-to-peer micro-lending platform built exclusively for Ashesi University students. It bridges the gap between stipends with trust, transparency, and identity protection.

---

## Key Features
* **Anonymous Peer-to-Peer Lending:** Users interact using generated aliases (e.g., *StarVault402*) to protect their identity while funding or requesting loans.
* **OTP Phone Verification:** Integrated with the Arkesel SMS API for secure, 6-digit OTP phone verification.
* **Capital Vaults:** Lenders can deploy capital with custom interest rates (0-15%) and durations.
* **Trust Tier System:** Gamified user profiles featuring a "Community Trust Score" to incentivize timely repayments.
* **Admin Dashboard:** A dedicated portal for administrators to review, approve, and reject pending loan applications.
* **Dockerized Architecture:** Fully containerized microservices environment for seamless local development and deployment.

---

## Tech Stack
* **Frontend:** React.js, Tailwind CSS, React Router
* **Backend:** PHP 8.x (Native APIs, PDO)
* **Database:** MySQL 8.0 (3rd Normal Form Schema)
* **DevOps:** Docker, Docker Compose
* **External APIs:** Arkesel SMS API

---

## Project Structure
```text
CharleeDash/
├── src/                        # React Frontend Source Code
│   ├── components/             # React Components (AdminDashboard, Dashboard, etc.)
│   └── App.jsx                 # React Router Setup
├── api/                        # PHP Backend (Root for port 8091)
│   ├── auth/                   # Authentication Endpoints
│   ├── vaults/                 # Vault & Lending Endpoints
│   ├── admin/                  # Admin Approval Endpoints
│   └── db.php                  # Singleton PDO Database Connection
├── charleedash_dump.sql        # MySQL Initialization Script (Schema)
├── docker-compose.yml          # Container Orchestration
└── README.md

---

## Installation & Setup
Prerequisites
Docker Desktop installed and running.