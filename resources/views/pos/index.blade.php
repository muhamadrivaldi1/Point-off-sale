@extends('layouts.app')
<button class="btn btn-success mt-2" id="payBtn">Bayar</button>
</div>
</div>
<script>
let trxId = null;
fetch('/pos/start',{method:'POST',headers:{'X-CSRF-TOKEN':'{{ csrf_token() }}'}})
.then(res=>res.json()).then(d=>trxId=d.id);


document.getElementById('barcode').addEventListener('change',function(){
fetch('/pos/scan',{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':'{{ csrf_token() }}'},body:JSON.stringify({barcode:this.value})})
.then(res=>res.json()).then(d=>{
fetch('/pos/add-item',{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':'{{ csrf_token() }}'},body:JSON.stringify({transaction_id:trxId,product_unit_id:d.unit.id,qty:1})})
.then(res=>res.json()).then(item=>{
let row=document.createElement('tr');
row.innerHTML=`<td>${d.unit.product.name}</td><td>${item.qty}</td><td>${item.price}</td><td>${item.subtotal}</td>`;
document.querySelector('#cartTable tbody').appendChild(row);
});
});
this.value='';
});


document.getElementById('payBtn').onclick=function(){
fetch('/pos/pay',{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':'{{ csrf_token() }}'},body:JSON.stringify({transaction_id:trxId,paid:document.getElementById('paid').value})})
.then(()=>location.reload());
}
</script>
@endsection
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>@yield('title','POS')</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<nav class="navbar navbar-dark bg-dark px-3">
<span class="navbar-brand">POS System</span>
<form method="POST" action="/logout">@csrf<button class="btn btn-sm btn-danger">Logout</button></form>
</nav>
<div class="container mt-4">
@yield('content')
</div>
</body>
</html>