# Schools Selenium Tests

This directory contains Selenium tests for school-related functionality in the CHIL application.

## Tests

### book_appointment.py
Tests booking doctor appointments from a school context.

**What it does:**
- Navigates to school dashboard (school ID 1)
- Verifies school dashboard loads correctly
- Navigates to the "Book Doctor" page
- Opens the new appointment modal
- If no students exist, automatically creates a test student first
- Fills out the appointment booking form with:
  - Random future appointment time
  - Available doctor
  - Available duration
  - Test reason
- Submits the appointment booking
- Checks for success/error messages

**Requirements:**
- School with ID 1 must exist in the database
- At least one doctor must be available
- At least one duration must be configured
- Laravel application must be running on localhost:8000

**Status:** ✅ Working - Full end-to-end appointment booking with automatic student creation

### add_student.py
Tests adding new students to a school.

**What it does:**
- Navigates to school dashboard (school ID 1)
- Verifies school dashboard loads correctly
- Navigates to the "Students" page
- Opens the student registration modal
- Fills out the student form with:
  - Unique student name
  - Gender, grade, birth date
  - Parent contact information
- Submits the student registration
- Checks for success/error messages

**Requirements:**
- School with ID 1 must exist in the database
- Laravel application must be running on localhost:8000

**Status:** ✅ Working - Successfully handles complex form validation including gender field submission

**Technical Notes:**
- Uses JavaScript form manipulation for reliable field setting
- Includes workaround for gender field FormData issues
- Handles modal interactions and form validation

## Usage

```bash
# Run the student addition test
python selenium_tests/schools/add_student.py

# Run the appointment booking test
python selenium_tests/schools/book_appointment.py

# Run via the main test runner
cd selenium_tests/shared
python main_runner.py school-book
```

Or run directly:
```bash
python selenium_tests/schools/book_appointment.py
```

## Troubleshooting

### Gender Field Validation Issues
**Problem:** Student creation fails with "The gender field is required" even when gender is selected in the UI.

**Solution:** The tests now use JavaScript form manipulation and include a workaround that manually adds a hidden gender input to the form when FormData shows the field as empty. This handles cases where the select element's value isn't properly reflected in the form submission.

### Modal Interaction Issues
**Problem:** Modal forms don't open or submit properly.

**Solution:** Tests include proper waiting mechanisms and JavaScript clicks for reliable modal interactions. If issues persist, check that the Laravel application is running and the database is properly seeded.

### No Students/Doctors Available
**Problem:** Tests fail because no students or doctors exist in the database.

**Solution:** The appointment booking test automatically creates students when needed. Ensure your database has at least one doctor and duration configured. Check the Laravel seeders for proper data setup.