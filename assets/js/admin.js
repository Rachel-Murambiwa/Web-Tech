const users = [
  { name: "Rachel Murambiwa", role: "Student" },
  { name: "John Doe", role: "Student" },
  { name: "Dr. Smith", role: "Faculty" },
  { name: "Admin Jane", role: "Admin" }
];

function viewUsers() {
  const list = document.getElementById("userList");
  list.innerHTML = "";

  users.forEach(u => {
    const li = document.createElement("li");
    li.textContent = `${u.name} — ${u.role}`;
    list.appendChild(li);
  });
}

function backupData() {
  alert("System backup completed successfully!");
}

function viewLogs() {
  alert("Viewing system activity logs in console.");
  console.log("Activity Logs:");
  console.log("- Rachel marked attendance");
  console.log("- Dr. Smith uploaded new materials");
  console.log("- Admin Jane created backup");
}
