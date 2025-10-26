#!/usr/bin/env python3
"""
Cleanup existing appointments test module for schools.
This module cleans up existing appointments by cancelling unpaid ones and deleting cancelled ones from the school appointments page.
"""

import sys
import os
sys.path.insert(0, os.path.join(os.path.dirname(__file__), '..', 'shared'))

from selenium.webdriver.common.by import By
from base_test import BaseTest

class SchoolCleanupAppointmentsTest(BaseTest):
    """Test class for cleaning up existing appointments from school dashboard"""

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
        """Navigate to school appointments page"""
        appointments_link = self.driver.find_element(By.LINK_TEXT, "Appointments")
        appointments_link.click()
        self.wait(5)
        print("Navigated to school appointments page")

    def run_test(self):
        """Run the school cleanup test"""
        print("Starting cleanup of existing school appointments...")

        try:
            # Navigate to school dashboard first
            self.navigate_to_school_dashboard()

            # Check if dashboard loaded
            if not self.check_school_dashboard_loaded():
                print("✗ School dashboard did not load properly")
                return False

            # Go to appointments page where cancelled appointments can be deleted
            self.navigate_to_school_appointments()

            # Find all appointment rows
            appointment_rows = self.driver.find_elements(By.CSS_SELECTOR, "tbody tr")
            cancelled_count = 0
            deleted_count = 0

            # Process appointments one by one to avoid modal conflicts
            while True:
                # Re-fetch appointment rows after each operation
                appointment_rows = self.driver.find_elements(By.CSS_SELECTOR, "tbody tr")
                found_action = False

                for row in appointment_rows:
                    try:
                        # Check appointment status
                        status_badge = row.find_element(By.CSS_SELECTOR, ".badge")
                        status_text = status_badge.text.lower().strip()

                        if status_text in ['pending', 'confirmed', 'awaiting_payment', 'paid']:
                            # Cancel unpaid appointments and confirmed ones that might be blocking
                            try:
                                cancel_btn = row.find_element(By.CSS_SELECTOR, ".btn-cancel-appointment")
                                if cancel_btn and cancel_btn.is_displayed():
                                    cancel_btn.click()
                                    self.wait(1)
                                    alert = self.driver.switch_to.alert
                                    alert.accept()
                                    self.wait(2)
                                    cancelled_count += 1
                                    print(f"Cancelled {status_text} appointment #{cancelled_count}")
                                    found_action = True
                                    break
                            except:
                                continue
                        elif status_text == 'cancelled':
                            # Delete cancelled appointments
                            try:
                                delete_btn = row.find_element(By.CSS_SELECTOR, ".delete-appointment")
                                if delete_btn and delete_btn.is_displayed():
                                    delete_btn.click()
                                    self.wait(1)
                                    # Submit the delete form
                                    delete_form = self.driver.find_element(By.ID, "deleteForm")
                                    delete_form.submit()
                                    self.wait(2)  # Wait for form submission

                                    # Force close modal if it's still open
                                    try:
                                        modal = self.driver.find_element(By.ID, "deleteAppointmentModal")
                                        if modal.is_displayed():
                                            close_btn = self.driver.find_element(By.CSS_SELECTOR, "#deleteAppointmentModal .close")
                                            close_btn.click()
                                            self.wait(1)
                                    except:
                                        pass

                                    deleted_count += 1
                                    print(f"Deleted cancelled appointment #{deleted_count}")
                                    found_action = True
                                    break
                            except Exception as e:
                                print(f"Error deleting appointment: {e}")
                                continue
                    except Exception as e:
                        continue

                if not found_action:
                    break  # No more appointments to process

            print(f"Cleaned up {cancelled_count} unpaid appointments and {deleted_count} cancelled appointments from school")
            return True

        except Exception as e:
            print(f"Warning: Could not clean up existing school appointments: {str(e)}")
            return False

def run_school_cleanup_test():
    """Standalone function to run the school cleanup test"""
    test = SchoolCleanupAppointmentsTest()
    try:
        test.setup_driver()
        return test.run_test()
    finally:
        test.teardown_driver()

if __name__ == "__main__":
    run_school_cleanup_test()