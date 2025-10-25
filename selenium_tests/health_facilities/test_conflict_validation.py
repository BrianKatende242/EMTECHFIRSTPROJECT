#!/usr/bin/env python3
"""
Conflict validation test module.
This module tests that booking appointments with time conflicts shows validation errors.
"""

from selenium.webdriver.common.by import By
from selenium.webdriver.support.ui import Select
from shared.base_test import BaseTest

class ConflictValidationTest(BaseTest):
    """Test class for testing time conflict validation"""

    def test_conflict_validation(self):
        """Test that conflicting appointments show validation errors"""
        print("Testing time conflict validation...")

        # Navigate to appointments page
        self.navigate_to_appointments()

        # Click New Appointment button
        new_appointment_btn = self.driver.find_element(By.CSS_SELECTOR, "button[data-target='#bookDoctorModal']")
        new_appointment_btn.click()
        self.wait(1)

        # Fill form with conflicting time (assuming there's already an appointment at this time)
        patient_select = self.driver.find_element(By.NAME, "patient_id")
        select_patient = Select(patient_select)
        select_patient.select_by_index(1)

        # Use the same datetime as existing appointments (365 days ahead at 7 AM)
        datetime_str = self.get_future_datetime_str()
        self.set_datetime_input(datetime_str)

        # Same doctor
        doctor_select = self.driver.find_element(By.ID, "doctor_id")
        select_doctor = Select(doctor_select)
        select_doctor.select_by_index(1)

        # Same duration
        duration_select = self.driver.find_element(By.NAME, "duration_id")
        select_duration = Select(duration_select)
        select_duration.select_by_index(1)

        # Different reason
        reason_input = self.driver.find_element(By.NAME, "reason")
        reason_input.send_keys("Conflicting appointment test")

        # Submit
        submit_btn = self.driver.find_element(By.XPATH, "//button[@type='submit' and contains(text(), 'Book')]")
        submit_btn.click()
        self.wait(3)

        # Check if modal is still open with conflict error
        if self.check_modal_errors():
            # Check specifically for conflict error
            error_elements = self.driver.find_elements(By.CLASS_NAME, "text-danger")
            conflict_found = False
            for error in error_elements:
                if "conflicts" in error.text.lower():
                    print("✓ Time conflict validation working:", error.text)
                    conflict_found = True
                    break
            if not conflict_found:
                print("✗ Expected conflict error not found")
                return False
            return True
        else:
            print("✗ Modal closed unexpectedly - conflict validation may not be working")
            return False

    def run_test(self):
        """Run the conflict validation test"""
        print("Starting conflict validation test...")

        try:
            # Navigate to dashboard first
            self.navigate_to_health_facility_dashboard()
            return self.test_conflict_validation()
        except Exception as e:
            print(f"✗ Error during conflict validation test: {str(e)}")
            return False

def run_conflict_validation_test():
    """Standalone function to run the conflict validation test"""
    test = ConflictValidationTest()
    try:
        test.setup_driver()
        return test.run_test()
    finally:
        test.teardown_driver()

if __name__ == "__main__":
    run_conflict_validation_test()