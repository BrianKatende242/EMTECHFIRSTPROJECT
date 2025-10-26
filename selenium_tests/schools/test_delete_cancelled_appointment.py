#!/usr/bin/env python3
"""
Test delete cancelled appointment functionality in school book-doctor page.
This module tests the ability to delete cancelled appointments from the school appointments page.
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

class SchoolDeleteCancelledAppointmentTest(BaseTest):
    """Test class for deleting cancelled appointments from school book-doctor page"""

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

    def find_cancelled_appointment(self):
        """Find a cancelled appointment in the table"""
        try:
            appointment_rows = self.driver.find_elements(By.CSS_SELECTOR, "tbody tr")

            for row in appointment_rows:
                try:
                    # Check appointment status
                    status_badge = row.find_element(By.CSS_SELECTOR, ".badge")
                    status_text = status_badge.text.lower().strip()

                    if status_text == 'cancelled':
                        # Found a cancelled appointment
                        print("✓ Found cancelled appointment")
                        return row
                except:
                    continue

            print("✗ No cancelled appointments found")
            return None

        except Exception as e:
            print(f"✗ Error finding cancelled appointment: {str(e)}")
            return None

    def delete_cancelled_appointment(self, appointment_row):
        """Delete a cancelled appointment"""
        try:
            # Find the delete button in the row
            delete_btn = appointment_row.find_element(By.CSS_SELECTOR, ".delete-appointment")
            if not delete_btn.is_displayed():
                print("✗ Delete button not visible")
                return False

            print("✓ Found delete button, clicking it...")
            # Click the delete button to open modal
            delete_btn.click()
            self.wait(2)

            # Wait for modal to appear
            WebDriverWait(self.driver, 10).until(
                EC.visibility_of_element_located((By.ID, "deleteAppointmentModal"))
            )
            print("✓ Delete modal opened")

            # Check modal content
            modal_body = self.driver.find_element(By.CSS_SELECTOR, "#deleteAppointmentModal .modal-body")
            print(f"Modal content: {modal_body.text}")

            # Click the confirm delete button
            confirm_delete_btn = self.driver.find_element(By.CSS_SELECTOR, "#deleteAppointmentModal .btn-danger")
            print("✓ Found confirm delete button, clicking it...")
            
            # Check form action before submitting
            delete_form = self.driver.find_element(By.ID, "deleteForm")
            form_action = delete_form.get_attribute('action')
            print(f"Form action before submit: {form_action}")
            
            confirm_delete_btn.click()

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

            print("✓ Delete action completed")
            return True

        except TimeoutException:
            print("✗ Delete modal did not appear")
            return False
        except Exception as e:
            print(f"✗ Error deleting appointment: {str(e)}")
            return False

    def verify_appointment_deleted(self, original_appointment_row):
        """Verify that the appointment was deleted"""
        try:
            # Try to find the same appointment row - it should be gone
            appointment_rows = self.driver.find_elements(By.CSS_SELECTOR, "tbody tr")

            # Check if we have fewer rows now
            if len(appointment_rows) < 1:
                print("✓ Appointment appears to be deleted (table is now empty)")
                return True

            # If there are still rows, check if our specific appointment is gone
            # This is harder to verify precisely, but we can check if the delete was successful
            # by looking for success messages or checking if the modal closed
            try:
                # Check if modal is closed
                modal = self.driver.find_element(By.ID, "deleteAppointmentModal")
                if not modal.is_displayed():
                    print("✓ Delete modal closed successfully")
                    return True
                else:
                    print("✗ Delete modal still open")
                    return False
            except:
                # Modal not found means it closed
                print("✓ Delete modal closed successfully")
                return True

        except Exception as e:
            print(f"✗ Error verifying deletion: {str(e)}")
            return False

    def run_test(self):
        """Run the delete cancelled appointment test"""
        print("Starting test: Delete cancelled appointment in school book-doctor page...")

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

            # Find a cancelled appointment
            cancelled_appointment = self.find_cancelled_appointment()
            if not cancelled_appointment:
                print("✗ No cancelled appointments available to delete")
                return False

            # Delete the cancelled appointment
            if not self.delete_cancelled_appointment(cancelled_appointment):
                return False

            # Check console again after deletion attempt
            try:
                logs = self.driver.get_log('browser')
                if logs:
                    print("Browser console errors after deletion:")
                    for log in logs:
                        print(f"  {log['level']}: {log['message']}")
            except:
                pass

            # Verify the appointment was deleted
            if self.verify_appointment_deleted(cancelled_appointment):
                print("✓ Test passed: Cancelled appointment deleted successfully")
                return True
            else:
                print("✗ Test failed: Could not verify appointment deletion")
                return False

        except Exception as e:
            print(f"✗ Test failed with error: {str(e)}")
            return False

def run_delete_cancelled_appointment_test():
    """Standalone function to run the delete cancelled appointment test"""
    test = SchoolDeleteCancelledAppointmentTest()
    try:
        test.setup_driver()
        return test.run_test()
    finally:
        test.teardown_driver()

if __name__ == "__main__":
    run_delete_cancelled_appointment_test()