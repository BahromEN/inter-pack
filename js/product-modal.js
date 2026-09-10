(function(){
  "use strict";

  var PH_ICON = '<svg viewBox="0 0 64 64" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M8 20 32 10 56 20 32 30Z"/><path d="M8 20V46L32 56V30"/><path d="M56 20V46L32 56"/></svg>';
  var currentId = null;

  function render(id){
    var items = window.ipT("products.items");
    var item = items[id];
    var specLabels = window.ipT("products.specLabels");
    if(!item) return;

    document.querySelectorAll("[data-pd='name']").forEach(function(el){ el.textContent = item.name; });
    document.querySelectorAll("[data-pd='desc']").forEach(function(el){ el.textContent = item.desc; });

    var tint = "ph-" + ((id % 6) + 1);
    document.querySelectorAll("[data-pd='photo']").forEach(function(el){
      el.className = "ph " + tint;
      el.innerHTML = '<div class="ph-icon">' + PH_ICON + '</div>';
    });

    var specsWrap = document.querySelector("[data-pd='specs']");
    if(specsWrap){
      specsWrap.innerHTML = "";
      item.specs.forEach(function(pair){
        var label = specLabels[pair[0]] || pair[0];
        var li = document.createElement("li");
        var span = document.createElement("span");
        span.textContent = label;
        var b = document.createElement("b");
        b.textContent = pair[1];
        li.appendChild(span);
        li.appendChild(b);
        specsWrap.appendChild(li);
      });
    }

    var galleryWrap = document.querySelector("[data-pd='gallery']");
    if(galleryWrap){
      galleryWrap.innerHTML = "";
      for(var i = 0; i < 5; i++){
        var g = document.createElement("div");
        g.className = "ph ph-" + (((id + i) % 6) + 1);
        g.innerHTML = '<div class="ph-icon">' + PH_ICON + '</div>';
        galleryWrap.appendChild(g);
      }
    }
  }

  function openModal(id){
    currentId = id;
    render(id);
    var overlay = document.getElementById("productModal");
    if(!overlay) return;
    overlay.style.display = "flex";
    requestAnimationFrame(function(){
      overlay.classList.add("is-open");
      document.body.classList.add("modal-open");
    });
  }

  function closeModal(){
    var overlay = document.getElementById("productModal");
    if(!overlay) return;
    overlay.classList.remove("is-open");
    document.body.classList.remove("modal-open");
    setTimeout(function(){ overlay.style.display = "none"; }, 300);
  }

  document.addEventListener("DOMContentLoaded", function(){
    document.querySelectorAll("[data-open-product]").forEach(function(btn){
      btn.addEventListener("click", function(){
        openModal(parseInt(btn.getAttribute("data-open-product"), 10) || 0);
      });
    });

    var overlay = document.getElementById("productModal");
    if(overlay){
      overlay.addEventListener("click", function(e){
        if(e.target === overlay) closeModal();
      });
      overlay.querySelectorAll("[data-modal-close]").forEach(function(btn){
        btn.addEventListener("click", closeModal);
      });
      overlay.querySelectorAll("[data-modal-cta]").forEach(function(btn){
        btn.addEventListener("click", function(){
          closeModal();
          var target = document.getElementById(btn.getAttribute("data-modal-cta"));
          if(target) setTimeout(function(){ target.scrollIntoView({behavior:"smooth", block:"start"}); }, 250);
        });
      });
    }

    document.addEventListener("keydown", function(e){
      if(e.key === "Escape") closeModal();
    });
  });

  document.addEventListener("ip:langchange", function(){
    if(currentId !== null) render(currentId);
  });
})();
