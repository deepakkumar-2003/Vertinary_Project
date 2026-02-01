# API Documentation

Base URL: `http://localhost:5000/api`

## Authentication

All endpoints except `/auth/login` and `/auth/register` require authentication.

Include the JWT token in the Authorization header:
```
Authorization: Bearer <your-jwt-token>
```

---

## Authentication Endpoints

### Login
```http
POST /auth/login
```

**Request Body:**
```json
{
  "email": "admin@vet.com",
  "password": "admin123"
}
```

**Response:**
```json
{
  "token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...",
  "user": {
    "id": "uuid",
    "email": "admin@vet.com",
    "name": "Admin User",
    "role": "ADMIN"
  }
}
```

### Register
```http
POST /auth/register
```

**Request Body:**
```json
{
  "email": "newuser@vet.com",
  "password": "password123",
  "name": "New User",
  "role": "STAFF"
}
```

### Get Profile
```http
GET /auth/profile
```

**Response:**
```json
{
  "id": "uuid",
  "email": "admin@vet.com",
  "name": "Admin User",
  "role": "ADMIN",
  "isActive": true,
  "createdAt": "2024-01-01T00:00:00.000Z"
}
```

### Change Password
```http
POST /auth/change-password
```

**Request Body:**
```json
{
  "oldPassword": "admin123",
  "newPassword": "newpassword123"
}
```

---

## Customer Endpoints

### Get All Customers
```http
GET /customers?search=john&limit=50&offset=0
```

**Query Parameters:**
- `search` (optional): Search by name, email, or phone
- `limit` (optional): Number of records per page (default: 50)
- `offset` (optional): Number of records to skip (default: 0)

### Get Customer by ID
```http
GET /customers/:id
```

### Create Customer
```http
POST /customers
```

**Request Body:**
```json
{
  "name": "John Farmer",
  "phone": "+91-9876543210",
  "email": "john@farm.com",
  "address": "123 Farm Road, Village",
  "emergencyContact": "Jane Farmer",
  "emergencyPhone": "+91-9876543211"
}
```

### Update Customer
```http
PUT /customers/:id
```

### Delete Customer
```http
DELETE /customers/:id
```

---

## Animal Endpoints

### Get All Animals
```http
GET /animals?search=cow&ownerId=uuid&status=ACTIVE&limit=50&offset=0
```

**Query Parameters:**
- `search` (optional): Search by ID, name, species, or breed
- `ownerId` (optional): Filter by owner
- `status` (optional): Filter by status (ACTIVE, SOLD, DECEASED, TRANSFERRED)
- `limit` (optional): Number of records per page
- `offset` (optional): Number of records to skip

### Get Animal by ID
```http
GET /animals/:id
```

**Response includes:** owner, diseases, treatments, vaccinations, AI records, milk records

### Create Animal
```http
POST /animals
```

**Request Body:**
```json
{
  "animalId": "COW-001",
  "name": "Bessie",
  "species": "Cattle",
  "breed": "Holstein",
  "age": 5,
  "gender": "FEMALE",
  "color": "Black and White",
  "ownerId": "uuid",
  "status": "ACTIVE"
}
```

**Gender Options:** `MALE`, `FEMALE`
**Status Options:** `ACTIVE`, `SOLD`, `DECEASED`, `TRANSFERRED`

### Update Animal
```http
PUT /animals/:id
```

### Delete Animal
```http
DELETE /animals/:id
```

---

## Disease Endpoints

### Get All Diseases
```http
GET /diseases?animalId=uuid&status=ACTIVE&limit=50&offset=0
```

### Get Disease by ID
```http
GET /diseases/:id
```

### Create Disease
```http
POST /diseases
```

**Request Body:**
```json
{
  "animalId": "uuid",
  "diseaseName": "Foot and Mouth Disease",
  "symptoms": "Fever, blisters, difficulty eating",
  "diagnosisDate": "2024-01-15",
  "severity": "MODERATE",
  "images": [],
  "notes": "Initial diagnosis"
}
```

**Severity Options:** `MILD`, `MODERATE`, `SEVERE`, `CRITICAL`
**Status Options:** `ACTIVE`, `RECOVERING`, `CURED`

### Upload Disease Image
```http
POST /diseases/:id/upload
Content-Type: multipart/form-data
```

**Form Data:**
- `image`: Image file (JPEG, PNG)

---

## Treatment Endpoints

### Get All Treatments
```http
GET /treatments?animalId=uuid&status=ONGOING
```

### Create Treatment
```http
POST /treatments
```

**Request Body:**
```json
{
  "animalId": "uuid",
  "diseaseId": "uuid",
  "treatmentType": "Antibiotic Therapy",
  "medicine": "Penicillin",
  "dosage": "500mg",
  "frequency": "Twice daily",
  "duration": "7 days",
  "startDate": "2024-01-15",
  "notes": "Continue monitoring"
}
```

**Status Options:** `ONGOING`, `COMPLETED`, `DISCONTINUED`

---

## Vaccination Endpoints

### Get All Vaccinations
```http
GET /vaccinations?animalId=uuid&upcoming=true
```

### Get Upcoming Vaccinations
```http
GET /vaccinations/upcoming?days=30
```

### Create Vaccination
```http
POST /vaccinations
```

**Request Body:**
```json
{
  "animalId": "uuid",
  "vaccineName": "FMD Vaccine",
  "vaccineType": "Foot and Mouth Disease",
  "batchNumber": "BATCH-2024-001",
  "administeredDate": "2024-01-15",
  "nextDueDate": "2024-07-15",
  "notes": "Annual vaccination"
}
```

---

## AI Record Endpoints

### Get All AI Records
```http
GET /ai-records?animalId=uuid&status=CONFIRMED
```

### Get Upcoming Due Dates
```http
GET /ai-records/upcoming?days=30
```

### Create AI Record
```http
POST /ai-records
```

**Request Body:**
```json
{
  "animalId": "uuid",
  "aiDate": "2024-01-15",
  "bullDetails": "Bull #123 - Holstein",
  "checkupDate": "2024-02-15",
  "dueDate": "2024-10-15",
  "pregnancyConfirmed": false,
  "notes": "First AI attempt"
}
```

**Status Options:** `PENDING`, `CONFIRMED`, `FAILED`

---

## Milk Record Endpoints

### Get All Milk Records
```http
GET /milk-records?animalId=uuid&startDate=2024-01-01&endDate=2024-01-31
```

### Get Milk Summary
```http
GET /milk-records/summary?animalId=uuid&startDate=2024-01-01&endDate=2024-01-31
```

**Response:**
```json
{
  "totalMilk": "450.50",
  "totalMorning": "250.30",
  "totalEvening": "200.20",
  "averageFat": "4.2",
  "averageSnf": "8.5",
  "recordCount": 30
}
```

### Create Milk Record
```http
POST /milk-records
```

**Request Body:**
```json
{
  "animalId": "uuid",
  "date": "2024-01-15",
  "morningMilk": 8.5,
  "eveningMilk": 7.2,
  "fat": 4.2,
  "snf": 8.5,
  "notes": "Good quality"
}
```

---

## Loan Endpoints

### Get All Loans
```http
GET /loans?customerId=uuid&status=ACTIVE
```

### Create Loan
```http
POST /loans
```

**Request Body:**
```json
{
  "customerId": "uuid",
  "loanAmount": 50000,
  "interestRate": 12,
  "startDate": "2024-01-01",
  "dueDate": "2024-12-31",
  "notes": "Farm equipment loan"
}
```

**Status Options:** `ACTIVE`, `PAID`, `OVERDUE`, `DEFAULTED`

### Record Payment
```http
POST /loans/:id/payment
```

**Request Body:**
```json
{
  "amount": 5000
}
```

---

## Insurance Endpoints

### Get All Insurance Policies
```http
GET /insurance?customerId=uuid&status=ACTIVE
```

### Create Insurance
```http
POST /insurance
```

**Request Body:**
```json
{
  "customerId": "uuid",
  "animalTag": "COW-001",
  "policyNumber": "POL-2024-001",
  "provider": "XYZ Insurance",
  "policyType": "Livestock Insurance",
  "coverageAmount": 100000,
  "premium": 5000,
  "startDate": "2024-01-01",
  "endDate": "2024-12-31",
  "documents": []
}
```

**Status Options:** `ACTIVE`, `EXPIRED`, `CANCELLED`

---

## Claim Endpoints

### Get All Claims
```http
GET /insurance/claims/all?insuranceId=uuid&status=PENDING
```

### Create Claim
```http
POST /insurance/claims
```

**Request Body:**
```json
{
  "insuranceId": "uuid",
  "claimAmount": 25000,
  "reason": "Animal death due to disease",
  "documents": []
}
```

**Status Options:** `PENDING`, `APPROVED`, `REJECTED`, `PAID`

### Update Claim
```http
PUT /insurance/claims/:id
```

**Request Body:**
```json
{
  "status": "APPROVED",
  "approvedAmount": 25000,
  "remarks": "Claim approved after verification"
}
```

---

## Error Responses

All endpoints return standard error responses:

```json
{
  "error": "Error message description"
}
```

**Common HTTP Status Codes:**
- `200` - Success
- `201` - Created
- `400` - Bad Request
- `401` - Unauthorized
- `403` - Forbidden
- `404` - Not Found
- `500` - Internal Server Error

---

## Role-Based Access Control

**ADMIN** - Full access to all endpoints
**VETERINARIAN** - Access to medical records, treatments, vaccinations
**STAFF** - Limited access to data entry and basic operations
**FARM_OWNER** - Read access to own animals and records

---

## Rate Limiting

- **Limit**: 100 requests per 15 minutes per IP
- **Response when exceeded**: 429 Too Many Requests
