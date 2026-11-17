/* ==========================================================================
   INTER PACK — public blog rendering (list + single post), language-aware.
   ========================================================================== */

(function(){
  "use strict";

  function escapeHtml(str){
    var div = document.createElement("div");
    div.textContent = str == null ? "" : String(str);
    return div.innerHTML;
  }

  function formatDate(iso, lang){
    if(!iso) return "";
    var d = new Date(iso.replace(" ", "T"));
    if(isNaN(d.getTime())) return "";
    var locale = lang === "uz" ? "uz-UZ" : (lang === "en" ? "en-GB" : "ru-RU");
    try{
      return d.toLocaleDateString(locale, {year:"numeric", month:"long", day:"numeric"});
    }catch(e){
      return d.toISOString().slice(0,10);
    }
  }

  function basePrefix(){
    var el = document.querySelector("[data-blog-base]");
    return el ? el.getAttribute("data-blog-base") : "../";
  }

  function viewsLabel(n, lang){
    n = parseInt(n, 10) || 0;
    if(lang === "en") return n + " " + (n === 1 ? "view" : "views");
    if(lang === "uz") return n + " marta ko'rilgan";
    var mod10 = n % 10, mod100 = n % 100;
    var word;
    if(mod10 === 1 && mod100 !== 11) word = "просмотр";
    else if([2,3,4].indexOf(mod10) !== -1 && [12,13,14].indexOf(mod100) === -1) word = "просмотра";
    else word = "просмотров";
    return n + " " + word;
  }

  function cardHtml(p, base, lang){
    var img = p.cover_image ? (base + p.cover_image) : "";
    return '' +
      '<a class="blog-card" href="' + base + 'blog/post.php?slug=' + encodeURIComponent(p.slug) + '">' +
        (img ? '<div class="blog-card-img"><img src="' + img + '" alt="" loading="lazy"></div>' : '') +
        '<div class="blog-meta">' +
          '<time>' + escapeHtml(formatDate(p.published_at, lang)) + '</time>' +
          '<span class="blog-views">' + escapeHtml(viewsLabel(p.views, lang)) + '</span>' +
        '</div>' +
        '<h3>' + escapeHtml(p.title) + '</h3>' +
        '<p>' + escapeHtml(p.excerpt) + '</p>' +
      '</a>';
  }

  function renderList(force){
    var root = document.getElementById("blogList");
    if(!root) return;
    var lang = window.ipLang ? window.ipLang() : "ru";
    if(!force && root.getAttribute("data-ssr-lang") === lang) return;
    var base = basePrefix();

    fetch(base + "api/blog.php?lang=" + lang, {cache:"no-store"})
      .then(function(res){ return res.json(); })
      .then(function(posts){
        if(!posts || !posts.length){
          root.innerHTML = '<p class="blog-empty" data-i18n="blog.empty">' + escapeHtml(window.ipT ? window.ipT("blog.empty", lang) : "") + '</p>';
          return;
        }
        root.innerHTML = posts.map(function(p){ return cardHtml(p, base, lang); }).join("");
      })
      .catch(function(){
        root.innerHTML = '<p class="blog-empty">Не удалось загрузить статьи.</p>';
      });
  }

  function renderHomePreview(){
    var root = document.getElementById("homeBlogPreview");
    if(!root) return;
    var lang = window.ipLang ? window.ipLang() : "ru";
    var base = basePrefix();

    fetch(base + "api/blog.php?lang=" + lang, {cache:"no-store"})
      .then(function(res){ return res.json(); })
      .then(function(posts){
        if(!posts || !posts.length){
          root.innerHTML = "";
          return;
        }
        root.innerHTML = posts.slice(0, 3).map(function(p){ return cardHtml(p, base, lang); }).join("");
      })
      .catch(function(){
        root.innerHTML = "";
      });
  }

  function renderPost(force){
    var root = document.getElementById("blogPost");
    if(!root) return;
    var lang = window.ipLang ? window.ipLang() : "ru";
    if(!force && root.getAttribute("data-ssr-lang") === lang) return;
    var base = basePrefix();
    var params = new URLSearchParams(window.location.search);
    var slug = params.get("slug") || "";

    if(!slug){
      if(force) root.innerHTML = '<p class="blog-empty">Статья не найдена.</p>';
      return;
    }

    fetch(base + "api/blog.php?lang=" + lang + "&slug=" + encodeURIComponent(slug), {cache:"no-store"})
      .then(function(res){
        if(!res.ok) throw new Error("not found");
        return res.json();
      })
      .then(function(post){
        var img = post.cover_image ? (base + post.cover_image) : "";
        document.title = post.title + " — INTER PACK";
        root.innerHTML = '' +
          '<a class="blog-back" href="' + base + 'blog/"><span aria-hidden="true">&larr;</span> <span data-i18n="blog.back">' + escapeHtml(window.ipT ? window.ipT("blog.back", lang) : "") + '</span></a>' +
          '<div class="blog-meta">' +
            '<time>' + escapeHtml(formatDate(post.published_at, lang)) + '</time>' +
            '<span class="blog-views">' + escapeHtml(viewsLabel(post.views, lang)) + '</span>' +
          '</div>' +
          '<h1>' + escapeHtml(post.title) + '</h1>' +
          (img ? '<div class="blog-cover"><img src="' + img + '" alt=""></div>' : '') +
          '<div class="blog-body">' + escapeHtml(post.body) + '</div>';
      })
      .catch(function(){
        root.innerHTML = '<p class="blog-empty">Статья не найдена.</p>';
      });
  }

  var hasRenderedInitial = false;

  function renderAllInitial(){
    renderList(false);
    renderPost(false);
    renderHomePreview();
    hasRenderedInitial = true;
  }

  document.addEventListener("DOMContentLoaded", function(){
    if(window.ipContentReady && typeof window.ipContentReady.then === "function"){
      window.ipContentReady.then(renderAllInitial);
    } else {
      renderAllInitial();
    }
  });

  // main.js fires this once on initial load too (not just on an explicit
  // language switch) — ignore that first occurrence so the server-rendered
  // Russian content isn't immediately replaced by an identical JS render.
  document.addEventListener("ip:langchange", function(){
    if(!hasRenderedInitial) return;
    renderList(true);
    renderPost(true);
    renderHomePreview();
  });
})();
