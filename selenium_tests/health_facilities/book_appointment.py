#!/usr/bin/env python3
"""
Book appointment test module.
This module books a new appointment, and adds a patient if none exist.
"""

from selenium.webdriver.common.by import By
from selenium.webdriver.support.ui import Select
import time
from base_test import BaseTest

class BookAppointmentTest(BaseTest):
    """Test class for booking appointments"""

    def add_patient_if_needed(self):
        """Add a new patient if no patients exist"""
        print("No patients available - adding a new patient")

        # Close the booking modal first
        try:
            close_btn = self.driver.find_element(By.CSS_SELECTOR, ".modal .close")
            close_btn.click()
            self.wait(1)
        except:
            print("Could not close modal")

        # Navigate to patients page
        self.navigate_to_patients()

        # Click Add Patient button
        add_patient_btn = self.driver.find_element(By.CSS_SELECTOR, "button[data-target='#addPatientModal']")
        add_patient_btn.click()
        self.wait(1)

        # Fill the patient form
        name_input = self.driver.find_element(By.NAME, "name")
        name_input.send_keys(f"Test Patient {int(time.time())}")

        gender_select = self.driver.find_element(By.NAME, "gender")
        select_gender = Select(gender_select)
        select_gender.select_by_value("male")

        birth_date_input = self.driver.find_element(By.NAME, "birth_date")
        birth_date_input.send_keys("1990-01-01")

        contact_input = self.driver.find_element(By.NAME, "contact_number")
        contact_input.send_keys("123456789")

        # Submit
        save_btn = self.driver.find_element(By.XPATH, "//button[@type='submit' and contains(text(), 'Save')]")
        save_btn.click()
        self.wait(3)

        # Verify patient was added
        try:
            patient_row = self.driver.find_element(By.XPATH, "//td[contains(text(), 'Test Patient')]")
            print("✓ Patient successfully added")
            return True
        except:
            print("✗ Patient addition may have failed")
            return False

    def book_appointment(self):
        """Book a new appointment"""
        # Navigate to appointments page
        self.navigate_to_appointments()

        # Click New Appointment button
        new_appointment_btn = self.driver.find_element(By.CSS_SELECTOR, "button[data-target='#bookDoctorModal']")
        new_appointment_btn.click()
        self.wait(1)

        # Check if patients exist
        patient_select = self.driver.find_element(By.NAME, "patient_id")
        select_patient = Select(patient_select)

        if len(select_patient.options) <= 1:
            # No patients, add one first
            if not self.add_patient_if_needed():
                return False

            # Go back to appointments and try again
            self.navigate_to_appointments()
            new_appointment_btn = self.driver.find_element(By.CSS_SELECTOR, "button[data-target='#bookDoctorModal']")
            new_appointment_btn.click()
            self.wait(1)
            patient_select = self.driver.find_element(By.NAME, "patient_id")
            select_patient = Select(patient_select)

        # Select patient
        select_patient.select_by_index(1)
        print("Selected patient")

        # Set future datetime
        datetime_str = self.get_future_datetime_str()
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

        # Select duration
        duration_select = self.driver.find_element(By.NAME, "duration_id")
        select_duration = Select(duration_select)
        if len(select_duration.options) > 1:
            select_duration.select_by_index(1)
            print("Selected duration")
        else:
            print("✗ No durations available")
            return False

        # Enter reason
        reason_input = self.driver.find_element(By.NAME, "reason")
        reason_input.send_keys("Automated test appointment")

        # Submit
        submit_btn = self.driver.find_element(By.XPATH, "//button[@type='submit' and contains(text(), 'Book')]")
        submit_btn.click()
        self.wait(3)

        print("Appointment booking attempted")

        # Check result
        if self.check_modal_errors():
            print("✗ Booking failed - modal still open with errors")
            return False
        else:
            print("✓ Appointment booking may have succeeded")
            self.check_for_alerts()
            return True

    def run_test(self):
        """Run the book appointment test"""
        print("Starting book appointment test...")

        try:
            # Navigate to dashboard first
            self.navigate_to_health_facility_dashboard()

            # Book the appointment
            return self.book_appointment()

        except Exception as e:
            print(f"✗ Error during appointment booking: {str(e)}")
            return False

def run_book_appointment_test():
    """Standalone function to run the book appointment test"""
    test = BookAppointmentTest()
    try:
        test.setup_driver()
        return test.run_test()
    finally:
        test.teardown_driver()

if __name__ == "__main__":
    run_book_appointment_test()