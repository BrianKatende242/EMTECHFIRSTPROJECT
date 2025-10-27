#!/usr/bin/env python3
"""
Add student test for schools.
This module tests adding new students to a school.
"""

import sys
import os
sys.path.insert(0, os.path.join(os.path.dirname(__file__), '..', 'shared'))

from selenium.webdriver.common.by import By
from selenium.webdriver.support.ui import Select
import time
from base_test import BaseTest

class AddStudentTest(BaseTest):
    """Test class for adding students to a school"""

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

    def navigate_to_students_page(self):
        """Navigate to students page"""
        students_link = self.driver.find_element(By.LINK_TEXT, "Students")
        students_link.click()
        self.wait(3)
        print("Navigated to students page")

    def check_students_page_loaded(self):
        """Check if students page loaded successfully"""
        try:
            # Look for students page elements
            page_title = self.driver.find_element(By.XPATH, "//h2[contains(text(), 'Student Management')]")
            print("✓ Students page loaded successfully")
            return True
        except Exception as e:
            print(f"✗ Students page elements not found: {str(e)}")
            return False

    def add_student(self):
        """Add a new student"""
        # Click Register button
        try:
            register_btn = self.driver.find_element(By.CSS_SELECTOR, "button[data-target='#newStudentModal']")
            register_btn.click()
            self.wait(2)  # Wait for modal to open
            print("Clicked register button")
        except Exception as e:
            print(f"Could not click register button: {e}")
            return False

        # Wait for modal to be visible
        try:
            modal = self.driver.find_element(By.ID, "newStudentModal")
            if not modal.is_displayed():
                print("Modal not visible")
                return False
            print("Student modal opened successfully")
        except Exception as e:
            print(f"Modal not found: {e}")
            return False

        # Fill the student form
        try:
            # Ensure we're on the "New Student" tab and trigger the form toggle
            new_patient_radio = self.driver.find_element(By.ID, "new_patient")
            self.driver.execute_script("arguments[0].click();", new_patient_radio)
            self.wait(1)

            # Fill form fields - use JavaScript for all inputs including select
            self.driver.execute_script("""
                document.querySelector('input[name="name"]').value = 'Test Student """ + str(int(time.time())) + """';
                document.querySelector('input[name="grade"]').value = 'Grade 1';
                document.querySelector('input[name="birth_date"]').value = '2010-01-01';
                document.querySelector('input[name="parent_contact"]').value = '123456789';
                
                // Set gender select value using JavaScript
                const genderSelect = document.querySelector('select[name="gender"]');
                genderSelect.value = 'male';
                genderSelect.dispatchEvent(new Event('change'));
            """)

            # Debug: check what value is selected after JavaScript setting
            selected_value = self.driver.execute_script("return document.querySelector('select[name=\"gender\"]').value;")
            print(f"Gender select value after JS set: {selected_value}")

            # Also check if the field is required and has value
            is_required = self.driver.execute_script("return document.querySelector('select[name=\"gender\"]').hasAttribute('required');")
            print(f"Gender field required: {is_required}")
            
            # Debug: check the select options
            options = self.driver.execute_script("""
                const select = document.querySelector('select[name="gender"]');
                const opts = Array.from(select.options).map(opt => ({value: opt.value, text: opt.text}));
                return opts;
            """)
            print(f"Gender select options: {options}")

            print("Filled student form")

            # Debug: check form data before submission
            form_data = self.driver.execute_script("""
                const form = document.querySelector('#newStudentModal form');
                const formData = new FormData(form);
                const data = {};
                for (let [key, value] of formData.entries()) {
                    data[key] = value;
                }
                return data;
            """)
            print(f"Form data before submission: {form_data}")

            # If gender is empty in FormData, manually add it
            if form_data.get('gender') == '':
                print("Gender field is empty in FormData, manually setting it...")
                self.driver.execute_script("""
                    const form = document.querySelector('#newStudentModal form');
                    const hiddenInput = document.createElement('input');
                    hiddenInput.type = 'hidden';
                    hiddenInput.name = 'gender';
                    hiddenInput.value = 'male';
                    form.appendChild(hiddenInput);
                """)
                print("Added hidden gender input to form")

            # Submit
            submit_btn = self.driver.find_element(By.CSS_SELECTOR, "#newStudentModal button[type='submit']")
            submit_btn.click()
            self.wait(5)  # Wait for form submission

            print("Submitted student form")

            # Check for success message
            if self.check_for_alerts():
                print("✓ Student successfully added")
                return True
            else:
                print("✗ Student addition may have failed - checking for errors")
                # Check for validation errors
                error_elements = self.driver.find_elements(By.CLASS_NAME, "text-danger")
                if error_elements:
                    for error in error_elements:
                        print(f"Validation error: {error.text}")
                return False

        except Exception as e:
            print(f"Error filling student form: {e}")
            return False

    def run_test(self):
        """Run the add student test"""
        print("Starting add student test...")

        try:
            # Navigate to school dashboard first
            self.navigate_to_school_dashboard()

            # Check if dashboard loaded
            if not self.check_school_dashboard_loaded():
                print("✗ School dashboard did not load properly")
                return False

            # Navigate to students page
            self.navigate_to_students_page()

            # Check if students page loaded
            if not self.check_students_page_loaded():
                print("✗ Students page did not load properly")
                return False

            # Add the student
            return self.add_student()

        except Exception as e:
            print(f"✗ Error during student addition: {str(e)}")
            return False

def run_add_student_test():
    """Standalone function to run the add student test"""
    test = AddStudentTest()
    try:
        test.setup_driver()
        return test.run_test()
    finally:
        test.teardown_driver()

if __name__ == "__main__":
    run_add_student_test()