#!/usr/bin/env python3
"""
Main runner for selenium tests.
This script can run individual test modules or all tests in sequence.
"""

import sys
import os
import argparse

# Add the parent directory to Python path so we can import from health_facilities
sys.path.insert(0, os.path.dirname(os.path.dirname(os.path.abspath(__file__))))

from health_facilities.cleanup_appointments import run_cleanup_test
from health_facilities.check_dashboard import run_dashboard_check
from health_facilities.book_appointment import run_book_appointment_test
from health_facilities.test_cancellation import run_cancellation_test
from health_facilities.test_conflict_validation import run_conflict_validation_test
from health_facilities.test_cancelled_no_conflict import run_cancelled_no_conflict_test
from admin.test_admin_login import run_admin_login_test

def run_all_tests():
    """Run all tests in sequence"""
    print("Running all selenium tests...\n")

    tests = [
        ("Cleanup Appointments", run_cleanup_test),
        ("Dashboard Check", run_dashboard_check),
        ("Book Appointment", run_book_appointment_test),
        ("Cancellation Test", run_cancellation_test),
        ("Conflict Validation", run_conflict_validation_test),
        ("Cancelled No Conflict", run_cancelled_no_conflict_test),
        ("Admin Login Test", run_admin_login_test),
    ]

    results = []
    for test_name, test_func in tests:
        print(f"\n{'='*50}")
        print(f"Running: {test_name}")
        print('='*50)
        try:
            result = test_func()
            results.append((test_name, result))
            status = "PASSED" if result else "FAILED"
            print(f"\n{test_name}: {status}")
        except Exception as e:
            print(f"\n{test_name}: ERROR - {str(e)}")
            results.append((test_name, False))

    print(f"\n{'='*50}")
    print("TEST SUMMARY")
    print('='*50)

    passed = 0
    total = len(results)
    for test_name, result in results:
        status = "✓ PASS" if result else "✗ FAIL"
        print(f"{status}: {test_name}")
        if result:
            passed += 1

    print(f"\nResults: {passed}/{total} tests passed")

    if passed == total:
        print("🎉 All tests passed!")
        return True
    else:
        print("❌ Some tests failed")
        return False

def run_single_test(test_name):
    """Run a single test by name"""
    test_map = {
        "cleanup": ("Cleanup Appointments", run_cleanup_test),
        "dashboard": ("Dashboard Check", run_dashboard_check),
        "book": ("Book Appointment", run_book_appointment_test),
        "cancel": ("Cancellation Test", run_cancellation_test),
        "conflict": ("Conflict Validation", run_conflict_validation_test),
        "no-conflict": ("Cancelled No Conflict", run_cancelled_no_conflict_test),
        "admin-login": ("Admin Login Test", run_admin_login_test),
    }

    if test_name not in test_map:
        print(f"Unknown test: {test_name}")
        print("Available tests:")
        for key, (name, _) in test_map.items():
            print(f"  {key}: {name}")
        return False

    test_full_name, test_func = test_map[test_name]
    print(f"Running single test: {test_full_name}")
    print('='*50)

    try:
        result = test_func()
        status = "PASSED" if result else "FAILED"
        print(f"\n{test_full_name}: {status}")
        return result
    except Exception as e:
        print(f"\n{test_full_name}: ERROR - {str(e)}")
        return False

def main():
    """Main entry point"""
    parser = argparse.ArgumentParser(description="Run selenium tests")
    parser.add_argument(
        "test",
        nargs="?",
        choices=["all", "cleanup", "dashboard", "book", "cancel", "conflict", "no-conflict", "admin-login"],
        help="Test to run (default: all)"
    )
    parser.add_argument(
        "--list",
        action="store_true",
        help="List available tests"
    )

    args = parser.parse_args()

    if args.list:
        print("Available tests:")
        print("  all: Run all tests")
        print("  cleanup: Cleanup existing appointments")
        print("  dashboard: Check dashboard loading")
        print("  book: Book a new appointment")
        print("  cancel: Test appointment cancellation")
        print("  conflict: Test time conflict validation")
        print("  no-conflict: Test cancelled appointments don't create conflicts")
        print("  admin-login: Test admin user login")
        return

    test_to_run = args.test or "all"

    if test_to_run == "all":
        success = run_all_tests()
    else:
        success = run_single_test(test_to_run)

    sys.exit(0 if success else 1)

if __name__ == "__main__":
    main()