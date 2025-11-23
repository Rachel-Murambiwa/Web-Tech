function generatePin() {
  const pin = Math.floor(1000 + Math.random() * 9000);
  const display = document.getElementById("generatedPin");
  display.textContent = `Current Attendance PIN: ${pin}`;
  display.style.color = "#4CAF50";
  localStorage.setItem("currentAttendancePIN", pin);
}

function loadAttendanceList() {
  const list = document.getElementById("studentList");
  list.innerHTML = "";

  if (attendance.length === 0) {
    list.innerHTML = "<li>No students have registered attendance yet.</li>";
  } else {
    attendance.forEach(student => {
      const li = document.createElement("li");
      li.textContent = `${student.name} — PIN: ${student.pin}`;
      list.appendChild(li);
    });
  }
}

window.onload = loadAttendanceList;
