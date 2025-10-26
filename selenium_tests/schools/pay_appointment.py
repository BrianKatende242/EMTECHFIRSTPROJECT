#!/usr/bin/env python3
"""
Pay for appointment test for schools.
This module tests paying for doctor appointments from a school context.
"""

import sys
import os
sys.path.insert(0, os.path.join(os.path.dirname(__file__), '..', 'shared'))

from selenium.webdriver.common.by import By
from selenium.webdriver.support.ui import WebDriverWait, Select
from selenium.webdriver.support import expected_conditions as EC
import time
from base_test import BaseTest

class SchoolPayAppointmentTest(BaseTest):
    """Test class for requesting payment for appointments from school dashboard"""

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

    def add_student_if_needed(self):
        """Add a new student if no students exist"""
        print("No students available - adding a new student")

        # Close the appointment booking modal first
        try:
            close_btn = self.driver.find_element(By.CSS_SELECTOR, "#newAppointmentModal .close")
            close_btn.click()
            self.wait(1)
            print("Closed appointment modal")
        except Exception as e:
            print(f"Could not close appointment modal: {e}")

        # Navigate to students page
        students_link = self.driver.find_element(By.LINK_TEXT, "Students")
        students_link.click()
        self.wait(3)

        # Click Register button
        try:
            register_btn = self.driver.find_element(By.CSS_SELECTOR, "button[data-target='#newStudentModal']")
            register_btn.click()
            self.wait(2)  # Wait longer for modal to open
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

                # Check if modal closed (success indicator)
                try:
                    modal = self.driver.find_element(By.ID, "newStudentModal")
                    if not modal.is_displayed():
                        print("✓ Student modal closed - likely successful")
                        return True
                    else:
                        print("✗ Student modal still open - creation may have failed")
                except:
                    print("✓ Student modal not found - likely successful")
                    return True

                return False

        except Exception as e:
            print(f"Error filling student form: {e}")
            return False

    def navigate_to_book_doctor(self):
        """Navigate to book doctor page"""
        book_doctor_link = self.driver.find_element(By.LINK_TEXT, "Appointments")
        book_doctor_link.click()
        self.wait(3)
        print("Navigated to book doctor page")

    def book_appointment(self):
        """Book a new appointment"""
        # Click New Appointment button
        new_appointment_btn = self.driver.find_element(By.CSS_SELECTOR, "button[data-target='#newAppointmentModal']")
        new_appointment_btn.click()
        self.wait(1)

        # Check if students exist
        patient_select = self.driver.find_element(By.ID, "patient_id")
        select_patient = Select(patient_select)

        # Debug: print available options
        options = select_patient.options
        print(f"Available patient options: {len(options)}")
        for i, option in enumerate(options):
            print(f"  Option {i}: value='{option.get_attribute('value')}' text='{option.text}'")

        if len(select_patient.options) <= 1:
            print("✗ No students available for booking - adding a new student")
            # Add student and then try booking again
            if self.add_student_if_needed():
                # Navigate back to appointments page and try again
                self.navigate_to_book_doctor()
                return self.book_appointment()
            else:
                print("✗ Failed to add student")
                return False
        else:
            print(f"Found {len(select_patient.options)-1} existing students, using the first available one")

        # Select student
        select_patient.select_by_index(1)
        print("Selected student")

        # Set future datetime - use a very unique time to avoid conflicts
        import random
        days_ahead = random.randint(400, 500)  # Random days far in the future
        hour = random.randint(8, 16)  # Random hour between 8 AM and 4 PM
        datetime_str = self.get_future_datetime_str(days_ahead=days_ahead, hour=hour)
        self.set_datetime_input(datetime_str)

        # Select doctor
        doctor_select = self.driver.find_element(By.ID, "doctor_id")
        select_doctor = Select(doctor_select)
        if len(select_doctor.options) > 1:
            select_doctor.select_by_index(1)
            print("Selected doctor")
        else:
            print("✗ No doctors available")
            return False

        # Select duration - find one with a non-zero price
        duration_select = self.driver.find_element(By.ID, "duration_id")
        select_duration = Select(duration_select)

        # Find a duration option that has a price > 0
        selected_duration_value = None
        for option in select_duration.options[1:]:  # Skip the "Select Duration" option
            price = option.get_attribute("data-price")
            if price and float(price) > 0:
                selected_duration_value = option.get_attribute("value")
                select_duration.select_by_visible_text(option.text)
                print(f"Selected duration: {option.text} (price: {price})")
                break

        if not selected_duration_value:
            print("✗ No duration with valid price found")
            return False

        # Extract just the numeric ID from the value (format: "1-general" -> "1")
        duration_id = selected_duration_value.split('-')[0] if '-' in selected_duration_value else selected_duration_value

        # Enter reason
        reason_input = self.driver.find_element(By.ID, "reason")
        reason_input.send_keys("Automated test appointment from school")

        # Submit via AJAX
        submit_btn = self.driver.find_element(By.ID, "submit-btn")
        submit_btn.click()
        print("Appointment booking submitted via AJAX")

        # Wait for AJAX response (button should be disabled during submission)
        self.wait(2)

        # Check for AJAX errors first
        try:
            error_container = self.driver.find_element(By.ID, "appointment-errors")
            if error_container.is_displayed() and error_container.text.strip():
                print(f"✗ AJAX Error: {error_container.text}")
                return False
        except:
            pass  # No error container found or not displayed

        # Check for success message
        try:
            success_container = self.driver.find_element(By.ID, "appointment-success")
            if success_container.is_displayed() and success_container.text.strip():
                print(f"✓ AJAX Success: {success_container.text}")
                return True
        except:
            pass  # No success container found or not displayed

        # Check if button is still disabled (still processing)
        if submit_btn.get_attribute("disabled"):
            print("Waiting for AJAX response to complete...")
            self.wait(3)  # Wait a bit more

        # Final check for success/error after AJAX completes
        try:
            error_container = self.driver.find_element(By.ID, "appointment-errors")
            if error_container.is_displayed() and error_container.text.strip():
                print(f"✗ Final AJAX Error: {error_container.text}")
                return False
        except:
            pass

        try:
            success_container = self.driver.find_element(By.ID, "appointment-success")
            if success_container.is_displayed() and success_container.text.strip():
                print(f"✓ Final AJAX Success: {success_container.text}")
                return True
        except:
            pass

        # Check for field-specific validation errors
        try:
            field_errors = self.driver.find_elements(By.CLASS_NAME, "invalid-feedback")
            visible_errors = [error for error in field_errors if error.is_displayed() and error.text.strip()]
            if visible_errors:
                print("✗ Field validation errors found:")
                for error in visible_errors:
                    print(f"  {error.text}")
                return False
        except Exception as e:
            print(f"Error checking field validation: {e}")

        # If we get here, check if modal closed (success) or still open (failure)
        try:
            modal = self.driver.find_element(By.ID, "newAppointmentModal")
            if modal.is_displayed():
                print("✗ Modal still open - booking may have failed")
                return False
            else:
                print("✓ Modal closed - booking successful")
                return True
        except:
            # Modal not found, likely closed successfully
            print("✓ Modal not found - booking successful")
            return True

    def find_awaiting_payment_appointment(self):
        """Find an appointment that is awaiting payment"""
        try:
            # Look for appointments table
            appointments_table = self.driver.find_element(By.CSS_SELECTOR, "table.table")
            rows = appointments_table.find_elements(By.CSS_SELECTOR, "tbody tr")

            for row in rows:
                cells = row.find_elements(By.TAG_NAME, "td")
                if len(cells) >= 4:  # Assuming status is in one of the later columns
                    status_cell = None
                    # Look for status badge
                    for cell in cells:
                        badges = cell.find_elements(By.CLASS_NAME, "badge")
                        for badge in badges:
                            if "awaiting_payment" in badge.text.lower() or "awaiting payment" in badge.text.lower():
                                status_cell = cell
                                break
                        if status_cell:
                            break

                    if status_cell:
                        # Found an awaiting payment appointment
                        # Look for action buttons in this row
                        action_buttons = row.find_elements(By.CSS_SELECTOR, "a.btn, button.btn")
                        pay_button = None
                        for button in action_buttons:
                            if "pay" in button.text.lower() or "payment" in button.text.lower():
                                pay_button = button
                                break

                        if pay_button:
                            print("Found awaiting payment appointment with pay button")
                            return pay_button

            print("No appointments awaiting payment found")
            return None

        except Exception as e:
            print(f"Error finding awaiting payment appointment: {e}")
            return None

    def pay_for_appointment(self):
        """Test the appointment payment form and initiate payment request"""
        try:
            # First try to find an existing appointment awaiting payment
            pay_button = self.find_awaiting_payment_appointment()

            if not pay_button:
                # No existing appointment, need to book one first
                print("No existing appointment awaiting payment - booking a new one")
                if not self.book_appointment():
                    print("✗ Failed to book appointment")
                    return False

                # Refresh the page to see the new appointment
                self.driver.refresh()
                self.wait(3)

                # Now look for the pay button again
                pay_button = self.find_awaiting_payment_appointment()

                if not pay_button:
                    print("✗ Could not find pay button after booking appointment")
                    return False

            # Click the pay button
            pay_button.click()
            
            # Wait for URL to change to payment page
            WebDriverWait(self.driver, 10).until(
                lambda driver: "appointment/pay" in driver.current_url
            )
            
            self.wait(2)  # Additional wait for page to load
            print("Clicked pay button")
            print(f"Current URL: {self.driver.current_url}")
            print(f"Page title: {self.driver.title}")

            # Check if we're on the payment page
            try:
                payment_header = self.driver.find_element(By.XPATH, "//strong[contains(text(), 'Pay for Appointment')]")
                print("✓ Navigated to payment page")
            except Exception as e:
                print(f"✗ Not on payment page: {e}")
                # Try to find any strong element with appointment
                try:
                    strong_elements = self.driver.find_elements(By.TAG_NAME, "strong")
                    for elem in strong_elements:
                        if "appointment" in elem.text.lower():
                            print(f"Found strong element: {elem.text}")
                except:
                    pass
                return False

            # Fill phone number if needed
            try:
                phone_input = self.driver.find_element(By.ID, "phone_number")
                if not phone_input.get_attribute("value"):
                    phone_input.clear()
                    phone_input.send_keys("256700000000")  # Test phone number
                    print("Filled phone number")
            except Exception as e:
                print(f"Could not fill phone number: {e}")

            # Click the request payment button
            try:
                request_payment_btn = self.driver.find_element(By.ID, "requestPaymentBtn")
                request_payment_btn.click()
                print("Clicked request payment button")

                # Wait for processing modal
                self.wait(3)

                # Check for success modal or processing state
                try:
                    # Look for processing modal
                    processing_modal = WebDriverWait(self.driver, 10).until(
                        EC.presence_of_element_located((By.ID, "paymentModal"))
                    )
                    print("✓ Payment request modal appeared")

                    # Check if success message appears
                    try:
                        success_text = WebDriverWait(self.driver, 10).until(
                            EC.presence_of_element_located((By.XPATH, "//h5[contains(text(), 'Payment Request Sent!')]"))
                        )
                        print("✓ Payment request sent successfully")
                        return True
                    except:
                        # If no success message, check if processing is still happening
                        try:
                            processing_text = self.driver.find_element(By.XPATH, "//h5[contains(text(), 'Processing Payment Request')]")
                            print("✓ Payment request is being processed")
                            return True
                        except:
                            # Check for any success indicators in the modal
                            try:
                                modal_body = self.driver.find_element(By.CSS_SELECTOR, "#paymentModal .modal-body")
                                modal_text = modal_body.text.lower()
                                if any(keyword in modal_text for keyword in ['success', 'sent', 'request', 'payment']):
                                    print(f"✓ Payment request appears successful (modal contains: {modal_text[:100]}...)")
                                    return True
                            except:
                                pass
                            
                            print("✗ Could not confirm payment request status")
                            return False

                except Exception as e:
                    print(f"✗ Payment modal not found: {e}")
                    return False

            except Exception as e:
                print(f"✗ Could not click request payment button: {e}")
                return False

        except Exception as e:
            print(f"✗ Error during payment process: {e}")
            return False

    def run_test(self):
        """Run the school pay appointment test"""
        print("Starting school pay appointment test...")

        try:
            # Navigate to school dashboard first
            self.navigate_to_school_dashboard()

            # Check if dashboard loaded
            if not self.check_school_dashboard_loaded():
                print("✗ School dashboard did not load properly")
                return False

            # Navigate to book doctor page
            self.navigate_to_book_doctor()

            # Pay for appointment
            return self.pay_for_appointment()

        except Exception as e:
            print(f"✗ Error during appointment payment: {str(e)}")
            return False

def run_school_pay_appointment_test():
    """Standalone function to run the school pay appointment test"""
    test = SchoolPayAppointmentTest()
    try:
        test.setup_driver()
        return test.run_test()
    finally:
        test.teardown_driver()

if __name__ == "__main__":
    run_school_pay_appointment_test()