function markAttendance() {
      const pin = document.getElementById("pinInput").value;
      const msg = document.getElementById("attendanceMsg");
      if (pin.trim() === "") {
        msg.textContent = "Please enter a valid PIN.";
        msg.style.color = "red";
      } else {
        msg.textContent = "✅ Attendance successfully recorded!";
        msg.style.color = "green";
      }
    }