DROP DATABASE IF EXISTS LoanSystem;
CREATE DATABASE LoanSystem;
USE LoanSystem;

CREATE TABLE Users (
    User_ID       VARCHAR(8)   PRIMARY KEY,
    First_Name    VARCHAR(20)  NOT NULL,
    Last_Name     VARCHAR(20)  NOT NULL,
    Email         VARCHAR(60)  NOT NULL UNIQUE,
    Phone_Number  VARCHAR(15)  UNIQUE,
    User_Password VARCHAR(255) NOT NULL,
    BankName      VARCHAR(50),
    BankAccount   VARCHAR(12)  UNIQUE,
    Code_Name     VARCHAR(20)  UNIQUE,
    Credit_Score  FLOAT        CHECK (Credit_Score BETWEEN 0 AND 10),
    Is_Verified   BOOLEAN      NOT NULL DEFAULT FALSE
);

CREATE TABLE Loan (
    Loan_ID         INT           PRIMARY KEY,
    Borrower_ID     VARCHAR(8),
    Lender_ID       VARCHAR(8),
    Amount          DECIMAL(10,2) NOT NULL,
    Duration_Months INT           NOT NULL,
    Interest_Rate   DECIMAL(5,2)  NOT NULL,
    Loan_Status     ENUM('pending','approved','rejected','disbursed','settled'),
    Date_Requested  DATE,
    Date_Disbursed  DATE,
    Purpose         ENUM('healthcare','transportation','recreation','charity','other') NOT NULL,
    FOREIGN KEY (Borrower_ID) REFERENCES Users(User_ID),
    FOREIGN KEY (Lender_ID)   REFERENCES Users(User_ID)
);

CREATE TABLE RepaymentSchedule (
    Repayment_ID INT           PRIMARY KEY,
    Loan_ID      INT           NOT NULL,
    Due_Date     DATE          NOT NULL,
    Amount_Due   DECIMAL(10,2) NOT NULL,
    Loan_Status  ENUM('paid','pending','late') NOT NULL,
    FOREIGN KEY (Loan_ID) REFERENCES Loan(Loan_ID)
);

CREATE TABLE Transactions (
    Transaction_ID   INT  PRIMARY KEY AUTO_INCREMENT,
    Transaction_Type ENUM('loan release','repayment') NOT NULL,
    Loan_ID          INT,
    Transaction_Date DATE NOT NULL,
    FOREIGN KEY (Loan_ID) REFERENCES Loan(Loan_ID)
);

CREATE TABLE Verification (
    Verification_ID   INT          PRIMARY KEY,
    User_ID           VARCHAR(8),
    Email             VARCHAR(100) NOT NULL,
    Verification_Code VARCHAR(100),
    Expiry_Date       DATETIME,
    FOREIGN KEY (User_ID) REFERENCES Users(User_ID)
);