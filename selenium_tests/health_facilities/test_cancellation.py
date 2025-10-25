#!/usr/bin/env python3
"""
Cancellation test module.
This module tests cancelling unpaid appointments.
"""

from selenium.webdriver.common.by import By
from shared.base_test import BaseTest
from .book_appointment import BookAppointmentTest

class CancellationTest(BaseTest):
    """Test class for testing appointment cancellation"""

    def test_cancellation(self):
        """Test cancelling an unpaid appointment"""
        print("Testing appointment cancellation...")

        # First book an appointment for testing
        book_test = BookAppointmentTest()
        book_test.driver = self.driver  # Use the same driver
        if not book_test.book_appointment():
            print("✗ Could not book appointment for cancellation test")
            return False

        # Navigate to doctor's appointments page
        self.navigate_to_doctor_appointments()

        try:
            # Find appointment rows
            appointment_rows = self.driver.find_elements(By.CSS_SELECTOR, "tbody tr")
            if not appointment_rows:
                print("✗ No appointment rows found")
                return False

            # Get the first (most recent) appointment
            first_row = appointment_rows[0]

            # Find cancel button
            cancel_btn = first_row.find_element(By.CSS_SELECTOR, ".btn-cancel")
            if not cancel_btn:
                print("✗ No cancel button found for appointment")
                return False

            print("Found cancel button for unpaid appointment")

            # Click cancel button
            cancel_btn.click()
            self.wait(1)

            # Accept alert
            alert = self.driver.switch_to.alert
            alert.accept()
            self.wait(2)

            # Check if status changed to cancelled
            status_badge = first_row.find_element(By.CSS_SELECTOR, ".appt-status .badge")
            if "cancelled" in status_badge.text.lower():
                print("✓ Appointment successfully cancelled")
                success = True
            else:
                print("✗ Appointment status did not change to cancelled")
                success = False

            # Check if cancel button is removed
            try:
                cancel_btn_removed = first_row.find_element(By.CSS_SELECTOR, ".btn-cancel")
                print("✗ Cancel button still present after cancellation")
                success = False
            except:
                print("✓ Cancel button removed after cancellation")

            return success

        except Exception as e:
            print(f"✗ Error testing cancellation: {str(e)}")
            return False

    def run_test(self):
        """Run the cancellation test"""
        print("Starting cancellation test...")

        try:
            return self.test_cancellation()
        except Exception as e:
            print(f"✗ Error during cancellation test: {str(e)}")
            return False

def run_cancellation_test():
    """Standalone function to run the cancellation test"""
    test = CancellationTest()
    try:
        test.setup_driver()
        return test.run_test()
    finally:
        test.teardown_driver()

if __name__ == "__main__":
    run_cancellation_test()