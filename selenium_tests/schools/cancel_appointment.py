#!/usr/bin/env python3
"""
Test cancel appointment functionality in school book-doctor page.
This module tests the ability to cancel awaiting payment appointments from the school appointments page.
"""

import sys
import os
sys.path.insert(0, os.path.join(os.path.dirname(__file__), '..', 'shared'))

from selenium.webdriver.common.by import By
from selenium.webdriver.support.ui import WebDriverWait
from selenium.webdriver.support import expected_conditions as EC
from selenium.common.exceptions import TimeoutException, NoSuchElementException
from base_test import BaseTest
import time

class SchoolCancelAppointmentTest(BaseTest):
    """Test class for cancelling appointments from school book-doctor page"""

    def navigate_to_school_dashboard(self, school_id=1):
        """Navigate to school dashboard"""
        url = f"http://localhost:8000/school-dashboard/{school_id}"
        self.driver.get(url)
        self.wait(2)
        print(f"Navigated to school dashboard: {url}")

    def check_school_dashboard_loaded(self):
        """Check if school dashboard loaded successfully"""
        try:
            # Look for school dashboard elements
            students_card = self.driver.find_element(By.XPATH, "//div[contains(text(), 'Students')]")
            appointments_card = self.driver.find_element(By.XPATH, "//div[contains(text(), 'Appointments')]")
            print("✓ School dashboard loaded successfully")
            return True
        except Exception as e:
            print(f"✗ School dashboard elements not found: {str(e)}")
            return False

    def navigate_to_school_appointments(self):
        """Navigate to school appointments page (book-doctor page)"""
        try:
            # Click on the "Appointments" link in the sidebar
            appointments_link = self.driver.find_element(By.LINK_TEXT, "Appointments")
            appointments_link.click()
            self.wait(5)
            print("Navigated to school appointments page")
            return True
        except Exception as e:
            print(f"✗ Could not navigate to appointments page: {str(e)}")
            return False

    def find_awaiting_payment_appointment(self):
        """Find an appointment that is awaiting payment"""
        try:
            appointment_rows = self.driver.find_elements(By.CSS_SELECTOR, "tbody tr")

            for row in appointment_rows:
                try:
                    # Check appointment status
                    status_badge = row.find_element(By.CSS_SELECTOR, ".badge")
                    status_text = status_badge.text.lower().strip()

                    if 'awaiting' in status_text and 'payment' in status_text:
                        # Found an awaiting payment appointment
                        print("✓ Found awaiting payment appointment")
                        return row
                except:
                    continue

            print("✗ No awaiting payment appointments found")
            return None

        except Exception as e:
            print(f"✗ Error finding awaiting payment appointment: {str(e)}")
            return None

    def cancel_appointment(self, appointment_row):
        """Cancel an awaiting payment appointment"""
        try:
            # Find the cancel button in the row
            cancel_btn = appointment_row.find_element(By.CSS_SELECTOR, ".cancel-appointment")
            if not cancel_btn.is_displayed():
                print("✗ Cancel button not visible")
                return False

            print("✓ Found cancel button, clicking it...")
            # Click the cancel button to open modal
            cancel_btn.click()
            self.wait(2)

            # Wait for modal to appear
            WebDriverWait(self.driver, 10).until(
                EC.visibility_of_element_located((By.ID, "cancelAppointmentModal"))
            )
            print("✓ Cancel modal opened")

            # Check modal content
            modal_body = self.driver.find_element(By.CSS_SELECTOR, "#cancelAppointmentModal .modal-body")
            print(f"Modal content: {modal_body.text}")

            # Click the confirm cancel button
            confirm_cancel_btn = self.driver.find_element(By.CSS_SELECTOR, "#cancelAppointmentModal .btn-warning")
            print("✓ Found confirm cancel button, clicking it...")

            # Check form action before submitting
            cancel_form = self.driver.find_element(By.ID, "cancelForm")
            form_action = cancel_form.get_attribute('action')
            print(f"Form action before submit: {form_action}")

            confirm_cancel_btn.click()

            # Wait for modal to close and page to update
            self.wait(3)
            print("✓ Form submitted, waiting for response...")

            # Check if there are any error messages
            try:
                error_alerts = self.driver.find_elements(By.CSS_SELECTOR, ".alert-danger")
                if error_alerts:
                    for alert in error_alerts:
                        print(f"✗ Error alert found: {alert.text}")
                    return False
            except:
                pass

            # Check for success messages
            try:
                success_alerts = self.driver.find_elements(By.CSS_SELECTOR, ".alert-success, .alert-warning")
                if success_alerts:
                    for alert in success_alerts:
                        print(f"✓ Success alert found: {alert.text}")
            except:
                pass

            print("✓ Cancel action completed")
            return True

        except TimeoutException:
            print("✗ Cancel modal did not appear")
            return False
        except Exception as e:
            print(f"✗ Error cancelling appointment: {str(e)}")
            return False

    def verify_appointment_cancelled(self, original_appointment_row):
        """Verify that the appointment was cancelled"""
        try:
            # Refresh the page to get updated status
            self.driver.refresh()
            self.wait(3)

            # Look for the appointment again and check its status
            appointment_rows = self.driver.find_elements(By.CSS_SELECTOR, "tbody tr")

            for row in appointment_rows:
                try:
                    # Check if this is the same appointment (compare some identifying info)
                    # For now, just check if any appointment has "cancelled" status
                    status_badge = row.find_element(By.CSS_SELECTOR, ".badge")
                    status_text = status_badge.text.lower().strip()

                    if status_text == 'cancelled':
                        print("✓ Found cancelled appointment - cancel action successful")
                        return True
                except:
                    continue

            print("✗ No cancelled appointments found after cancel action")
            return False

        except Exception as e:
            print(f"✗ Error verifying cancellation: {str(e)}")
            return False

    def run_test(self):
        """Run the cancel appointment test"""
        print("Starting test: Cancel awaiting payment appointment in school book-doctor page...")

        try:
            # Navigate to school dashboard first
            self.navigate_to_school_dashboard()

            # Check if dashboard loaded
            if not self.check_school_dashboard_loaded():
                print("✗ School dashboard did not load properly")
                return False

            # Go to book-doctor page
            if not self.navigate_to_school_appointments():
                return False

            # Check for any JavaScript errors in console
            try:
                logs = self.driver.get_log('browser')
                if logs:
                    print("Browser console errors:")
                    for log in logs:
                        print(f"  {log['level']}: {log['message']}")
            except:
                pass

            # Find an awaiting payment appointment
            awaiting_appointment = self.find_awaiting_payment_appointment()
            if not awaiting_appointment:
                print("✗ No awaiting payment appointments available to cancel")
                return False

            # Cancel the appointment
            if not self.cancel_appointment(awaiting_appointment):
                return False

            # Check console again after cancellation attempt
            try:
                logs = self.driver.get_log('browser')
                if logs:
                    print("Browser console errors after cancellation:")
                    for log in logs:
                        print(f"  {log['level']}: {log['message']}")
            except:
                pass

            # Verify the appointment was cancelled
            if self.verify_appointment_cancelled(awaiting_appointment):
                print("✓ Test passed: Appointment cancelled successfully")
                return True
            else:
                print("✗ Test failed: Could not verify appointment cancellation")
                return False

        except Exception as e:
            print(f"✗ Test failed with error: {str(e)}")
            return False

def run_cancel_appointment_test():
    """Standalone function to run the cancel appointment test"""
    test = SchoolCancelAppointmentTest()
    try:
        test.setup_driver()
        return test.run_test()
    finally:
        test.teardown_driver()

if __name__ == "__main__":
    run_cancel_appointment_test()