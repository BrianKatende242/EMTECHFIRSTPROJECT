# Sele```
selenium_tes```
selenium_tests/
├── health_facilities/     # Health facility related tests
│   ├── book_appointment.py
│   ├── check_dashboard.py
│   ├── cleanup_appointments.py
│   ├── test_cancel_appointment.py
│   ├── test_cancellation.py
│   ├── test_cancelled_no_conflict.py
│   └── test_conflict_validation.py
├── admin/                 # 👑 Admin related tests
│   └── test_admin_login.py
├── shared/                # 🔧 Shared utilities and base classes
│   ├── base_test.py
│   ├── main_runner.py
│   ├── requirements.txt
│   └── README.md
└── __init__.py
```cilities/     # 🏥 Health facility related tests
│   ├── book_appointment.py
│   ├── check_dashboard.py
│   ├── cleanup_appointments.py
│   ├── test_cancel_appointment.py
│   ├── test_cancellation.py
│   ├── test_cancelled_no_conflict.py
│   └── test_conflict_validation.py
├── admin/                 # 👑 Admin related tests
│   └── test_admin_login.py
├── shared/                # 🔧 Shared utilities and base classes
│   ├── base_test.py
│   ├── main_runner.py
│   ├── requirements.txt
│   └── README.md
└── __init__.py
```folder contains modular selenium tests for the Laravel backend appointment system. Tests are organized by entity/domain for better maintainability.

## Directory Structure

```
selenium_tests/
├── health_facilities/     # Health facility related tests
│   ├── book_appointment.py
│   ├── check_dashboard.py
│   ├── cleanup_appointments.py
│   ├── test_cancel_appointment.py
│   ├── test_cancellation.py
│   ├── test_cancelled_no_conflict.py
│   └── test_conflict_validation.py
├── shared/                # Shared utilities and base classes
│   ├── base_test.py
│   ├── main_runner.py
│   ├── requirements.txt
│   └── README.md
└── __init__.py
```

## Test Modules by Entity

### Health Facilities (`health_facilities/`)
- `book_appointment.py`: Books a new appointment (adds patient if none exist)
- `check_dashboard.py`: Verifies health facility dashboard loads correctly
- `cleanup_appointments.py`: Cleans up existing appointments (cancels unpaid, deletes cancelled)
- `test_cancel_appointment.py`: Tests cancelling appointments with page refresh
- `test_cancellation.py`: Tests cancelling unpaid appointments
- `test_conflict_validation.py`: Tests time conflict validation
- `test_cancelled_no_conflict.py`: Tests that cancelled appointments don't create conflicts

### Admin (`admin/`)
- `test_admin_login.py`: Tests admin user login and dashboard access

## Usage

First, activate the virtual environment:
```bash
cd selenium_tests
source venv/bin/activate  # On Windows: venv\Scripts\activate
```

### Run all tests
```bash
python shared/main_runner.py all
```

### Run individual test
```bash
python shared/main_runner.py <test_name>
```

Available test names:
- `cleanup`: Cleanup existing appointments
- `dashboard`: Check dashboard loading
- `book`: Book a new appointment
- `cancel`: Test appointment cancellation
- `conflict`: Test time conflict validation
- `no-conflict`: Test cancelled appointments don't create conflicts
- `admin-login`: Test admin user login

### List available tests
```bash
python shared/main_runner.py --list
```

### Run individual test files directly
Each test module can also be run directly:
```bash
python health_facilities/<test_module>.py
```

## Requirements

- Python 3.x
- Selenium WebDriver
- Chrome browser
- ChromeDriver (should be in PATH)

## Setup

1. Create and activate virtual environment:
```bash
cd selenium_tests
python3 -m venv venv
source venv/bin/activate  # On Windows: venv\Scripts\activate
```

2. Install dependencies:
```bash
pip install -r shared/requirements.txt
```

## Notes

- Tests assume the Laravel server is running on `http://localhost:8000`
- Tests use health facility ID 1 and doctor ID 1 by default
- Each test module manages its own browser driver instance for independence
- Tests are organized by business entity for better maintainability