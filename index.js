//const apiUrl = "https://jsonplaceholder.typicode.com";
const apiUrl = "http://adresbuk.local/addressbook.php";

let selectedContact;

const newUserInputs = {
	name: "",
	address: "",
	city: "",
	phone: "",
	business: "",
	email: "",
	messanger: "",
	social: "",
	website: "",
};

const editUserInputs = {
	id: "",
	name: "",
	address: "",
	city: "",
	phone: "",
	business: "",
	email: "",
	messanger: "",
	social: "",
	website: "",
};

// Table
const table = document.getElementById("table");
const headers = table.querySelectorAll("th");

// Add dialog and inputs
const addDialog = document.getElementById("add");
const addInputs = addDialog.querySelectorAll("input");
const addForm = addDialog.querySelector("#addForm");

// Edit dialog and inputs
const editDialog = document.getElementById("edit");
const editInputs = editDialog.querySelectorAll("input");
const editForm = editDialog.querySelector("#editForm");

// Delete dialog
const deleteDialog = document.getElementById("delete");
const deleteButton = document.getElementById("deleteButton");

// Search input
const searchInput = document.getElementById("search");
searchInput.value = "";

// Get all users from api
function getAllCOntacts() {
	fetch(apiUrl, {
		method: "GET",
		headers: { "Content-type": "application/json;charset=UTF-8" },
	})
		.then((response) => {
			if (response.ok) {
				return response.json();
			} else {
				return Promise.reject(response);
			}
		})
		.then((data) => {
			const tableBody = table.querySelector("tbody");

			const renderRow = data
				.map((user) => {
					return `
								<tr id=${user.id}>
									<td>${user.name}</td>
									<td>${user.phone}</td>
									<td>${user.city}</td>
									<td>${user.email}</td>
									<td>
										<button id="${user.id}" class="edit-button" onclick="window.edit.show();">Edit</button>
										<button id="${user.id}" class="delete-button" onclick="window.delete.show();">Delete</button>
									</td>
								</tr>
								`;
				})
				.join("");

			tableBody.insertAdjacentHTML("beforeend", renderRow);

			setSelectedContact(data);
		})
		.catch((err) => console.error(`Something went wrong ${err}`));
}

getAllCOntacts();

// Add new user POST method
function addNewContact(values) {
	fetch(`${apiUrl}`, {
		method: "POST",
		body: JSON.stringify(values),
		headers: {
			"Content-type": "application/json;charset=UTF-8",
		},
	})
		.then((response) => {
			if (response.ok) {
				clearPrevData();
				getAllCOntacts();
				response.json();
			}
		})
		.then((json) => console.log(json))
		.catch((err) => console.error("Something went wrong", err));
}

// Edit user PUT method
function editContact(values, selected) {
	fetch(apiUrl, {
		method: "PUT",
		body: JSON.stringify(values),
		headers: {
			"Content-type": "application/json;charset=UTF-8",
		},
	})
		.then((response) => {
			if (response.ok) {
				clearPrevData();
				getAllCOntacts();
			}
		})
		.catch((err) => console.error("Something went wrong", err));
}

// Edit user DELETE method
function deleteContact(selected) {
	fetch(`${apiUrl}?del_id=${selected}`, {
		method: "DELETE",
	})
		.then((response) => {
			if (response.ok) {
				deleteDialog.close();
				clearPrevData();
				getAllCOntacts();
			}
		})
		.catch((err) => console.error("Something went wrong", err));
}

// Send search query to server
function sendSearchQuery(query) {
	if (query === "") return;

	let url = `${apiUrl}?search=${query}`;

	fetch(url, {
		method: "GET",
		headers: { "Content-type": "application/json" },
	})
		.then((response) => {
			if (response.ok) {
				return response.json();
			}
		})
		.then((data) => {
			if (data.length > 0) {
				const tableBody = table.querySelector("tbody");

				const renderRow = data
					.map((user) => {
						return `
					<tr id=${user.id}>
					<td>${user.name}</td>
					<td>${user.phone}</td>
					<td>${user.city}</td>
					<td>${user.email}</td>
					<td>
					<button id="${user.id}" class="edit-button" onclick="window.edit.show();">Edit</button>
					<button id="${user.id}" class="delete-button" onclick="window.delete.show();">Delete</button>
					</td>
					</tr>
					`;
					})
					.join("");

				tableBody.insertAdjacentHTML("beforeend", renderRow);

				setSelectedContact(data);
			}
		})
		.catch((err) => console.log("Nothing found"));
}

// Clears previous rows from the DOM
function clearPrevData() {
	const tableBody = table.querySelector("tbody");
	const rows = tableBody.querySelectorAll("tr");

	[].forEach.call(rows, (row) => {
		tableBody.removeChild(row);
	});
}

// Track sort directions
const directions = Array.from(headers).map((header) => {
	return "";
});

// Transform the content of given cell in given column
const transform = function (index, content) {
	// Get the data type of column
	const type = headers[index].getAttribute("data-type");
	switch (type) {
		case "number":
			return parseFloat(content);
		case "string":
		default:
			return content;
	}
};

// Sorting columns
const sortColumn = function (index) {
	const tableBody = table.querySelector("tbody");
	const rows = tableBody.querySelectorAll("tr");
	// Get the current direction
	const direction = directions[index] || "asc";

	// A factor based on the direction
	const multiplier = direction === "asc" ? 1 : -1;

	const newRows = Array.from(rows);

	newRows.sort((rowA, rowB) => {
		const cellA = rowA.querySelectorAll("td")[index].innerHTML;
		const cellB = rowB.querySelectorAll("td")[index].innerHTML;

		const a = transform(index, cellA);
		const b = transform(index, cellB);

		switch (true) {
			case a > b:
				return 1 * multiplier;
			case a < b:
				return -1 * multiplier;
			case a === b:
				return 0;
		}
	});

	// Remove old rows
	[].forEach.call(rows, (row) => {
		tableBody.removeChild(row);
	});

	// Reverse the direction
	directions[index] = direction === "asc" ? "desc" : "asc";

	// Append new row
	newRows.forEach(function (newRow) {
		tableBody.appendChild(newRow);
	});
};

// Sets selected contact for edit or delete
function setSelectedContact(prevData) {
	const editButtons = document.querySelectorAll(".edit-button");
	const deleteButton = document.querySelectorAll(".delete-button");

	[].forEach.call(editButtons, (button) => {
		button.addEventListener("click", (e) => {
			let { id } = e.target;

			selectedContact = id;

			prevData.forEach((contact) => {
				if (contact.id === parseInt(id)) {
					[].forEach.call(editInputs, (input) => {
						if (contact.hasOwnProperty(input.name) && typeof contact[input.name] !== "object") {
							input.value = contact[input.name];
							editUserInputs[input.name] = contact[input.name];
						} else {
							input.value = "";
							editUserInputs[input.name] = "";
						}
					});
				}
			});
		});
	});

	[].forEach.call(deleteButton, (button) => {
		button.addEventListener("click", (e) => {
			selectedContact = e.target.id;
		});
	});
}

[].forEach.call(headers, (header, index) => {
	header.addEventListener("click", () => {
		sortColumn(index);
	});
});

// Add inputs on change get value
[].forEach.call(addInputs, (input) => {
	input.addEventListener("change", (e) => {
		newUserInputs[e.target.name] = e.target.value;
	});
});

// Edit inputs on change get value
[].forEach.call(editInputs, (input) => {
	input.addEventListener("change", (e) => {
		editUserInputs[e.target.name] = e.target.value;
	});
});

// Submit new contact
addForm.addEventListener("submit", (e) => {
	e.preventDefault();

	addNewContact(newUserInputs);

	addForm.close();
});

// Submit edit contact
editForm.addEventListener("submit", (e) => {
	e.preventDefault();

	editContact(editUserInputs, selectedContact);
	editForm.close();
});

// Sumbit delete contact
deleteButton.addEventListener("click", (e) => {
	e.preventDefault();

	deleteContact(selectedContact);
});

// Search on change sending to server
searchInput.addEventListener("input", (e) => {
	let query = e.target.value.toLowerCase();
	clearPrevData();

	if (query === "") {
		getAllCOntacts();
	}

	sendSearchQuery(query);
});