const addComment = document.querySelector(".add-commentaire");
const replyComments = document.querySelectorAll(".monster-comment");
const comments = document.querySelectorAll(".monster-comment");

comments.forEach((comment) => {
  const commentId = comment.id;
  const userId = comment.dataset.userId;
  const likeElement = comment.querySelector(".comment-liked");
  const removeElement = comment.querySelector(".remove-comment");

  likeElement.addEventListener("click", async (e) => {
    e.preventDefault();
    e.stopPropagation();

    try {
      const request = await fetch("/api/comment/liked.php", {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
        },
        body: JSON.stringify({
          comment_id: commentId,
          user_id: userId,
        }),
      });

      if (request.status === 401) {
        showLoginPopup();
        return;
      }

      const data = await request.json();

      if (data.success) {
        likeElement.classList.toggle("active");
      }
    } catch (err) {
      console.error(err);
    }
  });

  removeElement?.addEventListener("click", async (e) => {
    e.preventDefault();
    e.stopPropagation();

    try {
      const request = await fetch("/api/comment/delete.php", {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
        },
        body: JSON.stringify({
          comment_id: commentId,
        }),
      });

      if (request.status === 401) {
        showLoginPopup();
        return;
      }

      const data = await request.json();

      if (data.success) {
        location.href = `/monster/?name=${monster_name}&success=message_deleted`;
      } else if (data.warning) {
        location.href = `/monster/?name=${monster_name}&warning=forbidden`;
      }
    } catch (err) {
      console.error(err);
    }
  });
});

addComment?.addEventListener("click", async () => showCommentPopup());

replyComments.forEach((comment) => {
  const parent = comment.id;
  const name = comment.querySelector("h6").textContent;
  comment.addEventListener("click", async () => showCommentPopup(parent, name));
});

async function showCommentPopup(parentId = undefined, name = undefined) {
  const popup = document.createElement("div");

  popup.className = "login-popup-overlay";

  popup.innerHTML = `
        <div class="login-popup">
          <h3>${name ? "Répondre à " + name : "Ecrire un commentaire"}</h3>

          <textarea id="msg-content" rows="4" maxlength="255" placeholder="Écris ton message…" required></textarea>
          <div id="msg-char-count">0 / 255</div>

          <div class="popup-actions">
            <button class="popup-login-btn popup-send-btn">
              Envoyer
            </button>

            <button class="popup-close-btn">
              Fermer
            </button>
          </div>
        </div>
      `;

  document.body.appendChild(popup);

  const content = document.getElementById("msg-content");
  const charCount = document.getElementById("msg-char-count");

  content.addEventListener("input", () => {
    const length = content.value.length;
    charCount.textContent = `${length} / 255`;
    charCount.classList.toggle("is-warning", length > 230);
  });

  popup.querySelector(".popup-close-btn").addEventListener("click", () => {
    popup.remove();
  });

  popup
    .querySelector(".popup-send-btn")
    .addEventListener("click", async (e) => {
      e.preventDefault();
      e.stopPropagation();

      try {
        const request = await fetch("/api/comment/send.php", {
          method: "POST",
          headers: {
            "Content-Type": "application/json",
          },
          body: JSON.stringify({
            parent_id: parentId,
            monster_name: monster_name,
            content: content.value,
          }),
        });

        if (request.status === 401) {
          showLoginPopup();
          return;
        }

        const data = await request.json();

        if (data.success) {
          location.href = `/monster/?name=${monster_name}&success=message_sent`;
        }
      } catch (err) {
        console.error(err);
      }
    });

  popup.addEventListener("click", (e) => {
    if (e.target === popup) {
      popup.remove();
    }
  });
}
