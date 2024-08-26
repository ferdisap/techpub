// jika post method, tambahkan object ke method.options di techpubroute
// tapi body method didapat dari route.params
onmessage = async function(event) {
  let response;
  let options = event.data.route.options ?? {};
  if(Object.values(event.data.route.method).find(v => v === 'POST')){
    options = event.data.route.options;
    options.method = "POST";
    options.body = event.data.route.params;
  }
  response = await fetch(event.data.route.url,options);
  this.postMessage(await response.text());
}