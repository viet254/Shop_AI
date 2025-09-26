/** TechShop Blue Frontend JS **/
const BASE = (window.location.pathname.includes("/techshop-blue/")) ? "/techshop-blue" : ""; // auto if in htdocs/techshop-blue
const API = BASE + "/api";

// Helpers
async function api(url, method="GET", data=null){
  const opts = { method, headers: {} };
  if(data){
    opts.headers["Content-Type"] = "application/x-www-form-urlencoded; charset=UTF-8";
    const params = new URLSearchParams();
    for(const k in data) params.append(k, data[k]);
    opts.body = params.toString();
  }
  const res = await fetch(url, opts);
  return res.json();
}
function fmtVND(n){ return (n||0).toLocaleString('vi-VN') + "₫"; }
function qs(s){ return document.querySelector(s); }
function qsa(s){ return Array.from(document.querySelectorAll(s)); }
function getParam(name){ return new URLSearchParams(location.search).get(name); }

// Auth state
async function refreshAuthUI(){
  const me = await api(API + "/auth/me.php");
  const navLogin = qs("#nav-login");
  const navProfile = qs("#nav-profile");
  if(me && me.data){
    if(navLogin) navLogin.style.display = "none";
    if(navProfile) navProfile.style.display = "inline-block";
  }else{
    if(navLogin) navLogin.style.display = "inline-block";
    if(navProfile) navProfile.style.display = "none";
  }
}
refreshAuthUI();

// Cart count
async function updateCartCount(){
  try {
    const cart = await api(API + "/cart/get.php");
    const count = (cart.data?.items||[]).reduce((s,i)=>s+i.qty,0);
    const el = qs("#cart-count");
    if(el) el.textContent = count;
  } catch(e){}
}
updateCartCount();

// Page behaviors
document.addEventListener("DOMContentLoaded", async () => {
  const path = location.pathname;

  // Index: load latest products
  if(path.endsWith("/index.html") || path.endsWith("/public/") || path.endsWith("/techshop-blue/") || path.endsWith("/")){
    loadProducts();
    const btn = qs("#btn-search");
    const inp = qs("#search-input");
    if(btn && inp){
      btn.onclick = ()=> loadProducts(inp.value);
    }
  }

  // Login
  if(path.endsWith("/login.html")){
    const f = qs("#form-login");
    f?.addEventListener("submit", async (e)=>{
      e.preventDefault();
      const fd = new FormData(f);
      const r = await api(API + "/auth/login.php", "POST", Object.fromEntries(fd));
      if(r.ok){
        // nếu role admin -> chuyển admin
        const me = r.data;
        if(me.role === "admin") location.href = "../admin/index.html";
        else location.href = "index.html";
      } else alert(r.error||"Đăng nhập thất bại");
    });
  }

  // Register
  if(path.endsWith("/register.html")){
    const f = qs("#form-register");
    f?.addEventListener("submit", async (e)=>{
      e.preventDefault();
      const fd = new FormData(f);
      const r = await api(API + "/auth/register.php", "POST", Object.fromEntries(fd));
      if(r.ok){
        alert("Đăng ký thành công!");
        location.href = "index.html";
      } else alert(r.error||"Đăng ký thất bại");
    });
  }

  // Profile
  if(path.endsWith("/profile.html")){
    const me = await api(API + "/auth/me.php");
    if(!me.data){ alert("Bạn cần đăng nhập."); location.href="login.html"; return; }
    const info = qs("#user-info");
    info.innerHTML = `<p><strong>${me.data.name}</strong> (${me.data.email}) - Vai trò: ${me.data.role}</p>`;
    const f = qs("#form-profile");
    f.name.value = me.data.name||"";
    f.phone.value = me.data.phone||"";
    f.address.value = me.data.address||"";
    f.addEventListener("submit", async (e)=>{
      e.preventDefault();
      const fd = new FormData(f);
      const r = await api(API + "/users/update_profile.php", "POST", Object.fromEntries(fd));
      alert(r.ok ? "Đã cập nhật" : (r.error||"Lỗi"));
    });
    qs("#btn-logout").onclick = async ()=>{
      await api(API + "/auth/logout.php");
      location.href="index.html";
    };
  }

  // Category
  if(path.endsWith("/category.html")){
    const cat = getParam("cat") || "0";
    await loadProducts("", cat);
  }

  // Product detail
  if(path.endsWith("/product.html")){
    const id = getParam("id");
    if(!id){ location.href="index.html"; return; }
    const res = await api(API + "/products/detail.php?id=" + id);
    if(!res.ok){ alert(res.error||"Không tìm thấy"); location.href="index.html"; return; }
    const p = res.data.product;
    const images = res.data.images || [];
    const details = res.data.details || {};
    const rel = res.data.related || [];

    const el = qs("#product-detail");
    const imgs = images.map(i=>`<img src="${i.image_url}" alt="img"/>`).join("");
    const priceHtml = `<div class="price">${fmtVND(p.price)}</div>`;
    el.innerHTML = `
      <div>
        <div class="images">${imgs||"<div class='badge'>Chưa có ảnh</div>"}</div>
      </div>
      <div>
        <h2>${p.name}</h2>
        <div>${priceHtml}</div>
        <p>${p.short_desc||""}</p>
        <div>${p.description||""}</div>
        <button id="add-cart">Thêm vào giỏ</button>
      </div>
    `;
    qs("#add-cart").onclick = async ()=>{
      const r = await api(API + "/cart/add.php", "POST", {product_id: p.id, qty: 1});
      if(r.ok){ updateCartCount(); alert("Đã thêm vào giỏ"); }
    };

    // reviews
    const rcon = qs("#reviews");
    (res.data.reviews||[]).forEach(rv=>{
      const div = document.createElement("div");
      div.className = "card";
      div.innerHTML = `<strong>${rv.user_name||"Ẩn danh"}</strong> - ⭐${rv.rating}/5<br>${rv.content||""}`;
      rcon.appendChild(div);
    });

    // related
    const relCon = qs("#related");
    relCon.innerHTML = rel.map(x=>cardHTML(x)).join("");
    relCon.addEventListener("click", (e)=>{
      const item = e.target.closest(".product");
      if(item){
        const pid = item.dataset.id;
        location.href = "product.html?id="+pid;
      }
    });

    // review form
    const fr = qs("#form-review");
    fr?.addEventListener("submit", async (e)=>{
      e.preventDefault();
      const fd = new FormData(fr);
      const payload = Object.fromEntries(fd);
      payload.product_id = p.id;
      const r = await api(API + "/reviews/add.php", "POST", payload);
      alert(r.ok ? "Đã gửi đánh giá" : (r.error||"Lỗi"));
      if(r.ok) location.reload();
    });
  }

  // Cart
  if(path.endsWith("/cart.html")){
    await loadCart();
    qs("#form-checkout")?.addEventListener("submit", async (e)=>{
      e.preventDefault();
      const fd = new FormData(e.target);
      const r = await api(API + "/orders/create.php", "POST", Object.fromEntries(fd));
      alert(r.ok ? ("Đặt hàng thành công: " + r.data.order_code) : (r.error||"Lỗi"));
      if(r.ok) location.href="index.html";
    });
  }

  // Admin
  if(path.endsWith("/admin/index.html")){
    // bảo vệ: chỉ admin
    const me = await api(API + "/auth/me.php");
    if(!me.data || me.data.role!=="admin"){
      alert("Chỉ admin mới truy cập."); location.href="../public/login.html"; return;
    }

    const form = document.getElementById("form-admin-product");
    form.addEventListener("submit", async (e)=>{
      e.preventDefault();
      const fd = new FormData(form);
      const r = await api(API + "/admin/products_save.php", "POST", Object.fromEntries(fd));
      alert(r.ok ? "Đã lưu" : (r.error||"Lỗi"));
      if(r.ok){ form.reset(); loadAdminProducts(); }
    });

    await loadAdminProducts();
    await loadAdminOrders();
  }
});

// Product card
function cardHTML(p){
  const img = p.cover || "./assets/img/noimg.png";
  return `
    <div class="product" data-id="${p.id}">
      <img src="${img}" alt="${p.name}"/>
      <div class="p-body">
        <div class="badge">${p.category_name || ""}</div>
        <div>${p.name}</div>
        <div class="price">${fmtVND(p.price)}</div>
        <a href="product.html?id=${p.id}" class="btn">Xem chi tiết</a>
      </div>
    </div>
  `;
}

// Load products to grid
async function loadProducts(q="", catId=0){
  const grid = qs("#product-grid");
  const url = new URL(API + "/products/list.php", location.origin);
  if(q) url.searchParams.set("q", q);
  if(catId && catId!=="0") url.searchParams.set("category_id", catId);
  const res = await fetch(url).then(r=>r.json());
  grid.innerHTML = (res.data?.items||[]).map(cardHTML).join("") || "<p>Chưa có sản phẩm</p>";
  grid.addEventListener("click", (e)=>{
    const item = e.target.closest(".product");
    if(item){
      const pid = item.dataset.id;
      location.href = "product.html?id="+pid;
    }
  });
}

// Load cart
async function loadCart(){
  const c = await api(API + "/cart/get.php");
  const wrap = qs("#cart-items");
  const items = c.data?.items || [];
  if(!items.length){ wrap.innerHTML="<p>Giỏ hàng trống.</p>"; return; }
  wrap.innerHTML = items.map(i=>`
    <div class="card">
      <strong>${i.name}</strong><br>
      SL: ${i.qty} - Giá: ${fmtVND(i.price)} - Tạm tính: ${fmtVND(i.subtotal)}
      <div><button class="danger" data-id="${i.id}">Xóa</button></div>
    </div>
  `).join("");
  wrap.addEventListener("click", async (e)=>{
    const id = e.target.dataset.id;
    if(id){
      const r = await api(API + "/cart/remove.php", "POST", {product_id:id});
      if(r.ok){ updateCartCount(); loadCart(); }
    }
  });
}

// Admin helpers
async function loadAdminProducts(){
  // tái sử dụng list API (trả về mới nhất)
  const r = await api(API + "/products/list.php?per=50");
  const box = document.getElementById("admin-products");
  box.innerHTML = (r.data?.items||[]).map(p=>`
    <div class="card">
      <strong>#${p.id}</strong> ${p.name} - ${fmtVND(p.price)}
      <div>
        <button onclick="editProduct(${p.id})">Sửa</button>
        <button class="danger" onclick="deleteProduct(${p.id})">Xóa</button>
      </div>
    </div>
  `).join("") || "<p>Trống</p>";
}
async function editProduct(id){
  const r = await api(API + "/products/detail.php?id="+id);
  if(!r.ok){ alert(r.error||"Lỗi"); return; }
  const p = r.data.product;
  const imgs = (r.data.images||[]).map(i=>i.image_url).join("\n");
  const f = document.getElementById("form-admin-product");
  f.id.value = p.id;
  f.name.value = p.name;
  f.category_id.value = p.category_id;
  f.price.value = p.price;
  f.discount.value = p.discount;
  f.stock.value = p.stock;
  f.status.value = p.status;
  f.short_desc.value = p.short_desc||"";
  f.description.value = p.description||"";
  f.images.value = imgs;
  window.scrollTo({top:0, behavior:"smooth"});
}
async function deleteProduct(id){
  if(!confirm("Xóa sản phẩm?")) return;
  const r = await api(API + "/admin/products_delete.php", "POST", {id});
  alert(r.ok ? "Đã xóa" : (r.error||"Lỗi"));
  if(r.ok) loadAdminProducts();
}
async function loadAdminOrders(){
  const r = await api(API + "/admin/orders_list.php");
  const box = document.getElementById("admin-orders");
  box.innerHTML = (r.data||[]).map(o=>`
    <div class="card">
      <strong>${o.order_code}</strong> - ${fmtVND(o.total)} - ${o.status} - KH: ${o.customer||""}
      <div>
        <select id="st-${o.id}">
          <option>pending</option><option>paid</option><option>shipped</option><option>completed</option><option>cancelled</option>
        </select>
        <button onclick="updateOrder(${o.id})">Cập nhật</button>
      </div>
    </div>
  `).join("") || "<p>Trống</p>";
}
async function updateOrder(id){
  const st = qs("#st-"+id).value;
  const r = await api(API + "/admin/orders_update_status.php", "POST", {id, status: st});
  alert(r.ok ? "Đã cập nhật" : (r.error||"Lỗi"));
}

// Chatbox
(function(){
  const fab = qs("#chat-fab");
  const box = qs("#chatbox");
  const body = qs("#chat-body");
  const closeBtn = qs("#chat-close");
  const input = qs("#chat-text");
  const send = qs("#chat-send");

  if(!fab || !box) return;
  fab.onclick = ()=> box.style.display = "flex";
  closeBtn.onclick = ()=> box.style.display = "none";
  send.onclick = async ()=>{
    const text = input.value.trim();
    if(!text) return;
    appendMsg(text, "user");
    input.value = "";
    const r = await api(API + "/chat/ai.php", "POST", {q: text});
    if(r.data?.suggestions?.length){
      appendMsg(r.data.answer || "Gợi ý:", "ai");
      r.data.suggestions.forEach(s=>{
        appendMsg(`• ${s.name} — ${fmtVND(s.price)}`, "ai");
      });
    } else {
      appendMsg(r.data?.answer || "Xin lỗi, mình chưa rõ nhu cầu.", "ai");
    }
  };
  function appendMsg(text, who="ai"){
    const div = document.createElement("div");
    div.className = "msg " + who;
    div.textContent = text;
    body.appendChild(div);
    body.scrollTop = body.scrollHeight;
  }
})();
