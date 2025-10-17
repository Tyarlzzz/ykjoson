document.addEventListener("DOMContentLoaded", () => {
  const modal = document.getElementById("statusModal");
  const closeModal = document.getElementById("closeModal");
  const openButtons = document.querySelectorAll(".openStatusModal");

  openButtons.forEach((btn) => {
    btn.addEventListener("click", () => {
      modal.classList.remove("hidden");
    });
  });

  closeModal.addEventListener("click", () => {
    modal.classList.add("hidden");
  });

  // Close when clicking outside the modal content
  modal.addEventListener("click", (e) => {
    if (e.target === modal) modal.classList.add("hidden");
  });
});