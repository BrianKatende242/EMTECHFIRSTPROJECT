#!/usr/bin/env python3
"""
Cancelled no conflict test module.
This module tests that cancelled appointments don't create time conflicts.
"""

from selenium.webdriver.common.by import By
from selenium.webdriver.support.ui import Select
from shared.base_test import BaseTest
from .test_cancellation import CancellationTest

class CancelledNoConflictTest(BaseTest):
    """Test class for testing that cancelled appointments don't create conflicts"""

    def test_cancelled_no_conflict(self):
        """Test that cancelled appointments don't prevent booking at same time"""
        print("Testing that cancelled appointments don't create conflicts...")

        # First cancel an existing appointment
        cancel_test = CancellationTest()
        cancel_test.driver = self.driver  # Use same driver
        if not cancel_test.test_cancellation():
            print("✗ Could not cancel appointment for conflict test")
            return False

        # Now try to book another appointment at the same time
        self.navigate_to_appointments()

        # Click New Appointment button
        new_appointment_btn = self.driver.find_element(By.CSS_SELECTOR, "button[data-target='#bookDoctorModal']")
        new_appointment_btn.click()
        self.wait(1)

        # Fill form with same datetime (should NOT conflict since we cancelled)
        patient_select = self.driver.find_element(By.NAME, "patient_id")
        select_patient = Select(patient_select)
        select_patient.select_by_index(1)

        # Use same datetime
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
        reason_input.send_keys("Test booking after cancellation")

        # Submit
        submit_btn = self.driver.find_element(By.XPATH, "//button[@type='submit' and contains(text(), 'Book')]")
        submit_btn.click()
        self.wait(3)

        # Check if booking succeeded (modal should close)
        if self.check_modal_errors():
            print("✗ Booking failed after cancellation - modal still open")
            error_elements = self.driver.find_elements(By.CLASS_NAME, "text-danger")
            for error in error_elements:
                print(f"Error: {error.text}")
            return False
        else:
            print("✓ Booking succeeded after cancellation - cancelled appointments don't create conflicts")
            self.check_for_alerts()
            return True

    def run_test(self):
        """Run the cancelled no conflict test"""
        print("Starting cancelled no conflict test...")

        try:
            # Navigate to dashboard first
            self.navigate_to_health_facility_dashboard()
            return self.test_cancelled_no_conflict()
        except Exception as e:
            print(f"✗ Error during cancelled no conflict test: {str(e)}")
            return False

def run_cancelled_no_conflict_test():
    """Standalone function to run the cancelled no conflict test"""
    test = CancelledNoConflictTest()
    try:
        test.setup_driver()
        return test.run_test()
    finally:
        test.teardown_driver()

if __name__ == "__main__":
    run_cancelled_no_conflict_test()