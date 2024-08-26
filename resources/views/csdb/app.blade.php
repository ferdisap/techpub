{{-- 
  // tetap jalan web socketnya walaupun pakai iframe
  iframe = document.createElement('iframe');
  iframe.src = 'http://127.0.0.1:8000/csdb4/explorer';
  iframe.style.width = '100%';
  iframe.style.height = '100%';
  document.body.append(iframe); 
--}}
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="ie=edge">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />
  
  {{-- <script type="text/javascript" src="https://code.jquery.com/jquery-3.3.1.min.js"></script> --}}
  {{-- <script type="text/javascript" src="/js/jquery.maphilight.min.js"></script> --}}
  <script defer src="https://use.fontawesome.com/releases/v5.0.7/js/all.js"></script>

  {{-- <script src="/js/csdb/worker.js" type="module"></script> --}}

  {{-- <script>
    if(window.Worker){
      // window.w = new Worker("/js/worker.js");
      // window.w = new Worker("/worker");
      window.w = new Worker("/js/csdb/worker.js",{type:'module'});

      w.onmessage = function(){
        console.log('aaa');
      }
    }
  </script> --}}
  {{--
      untuk vue 
      gunakan @@vite instead of @vite jika tidak ingin pakai blade @vite directive 
      tapi saat vite production, tidak bisa otomatis ngarah ke build path
  --}}
  {{-- <script type="module" src="https://localhost:987/@@vite/client"></script>
  <link rel="stylesheet" href="https://localhost:987/resources/css/dmodule.css">
  <link rel="stylesheet" href="https://localhost:987/resources/css/app.css">
  <link rel="stylesheet" href="https://localhost:987/resources/css/loadingbar.css">
  <script src="https://localhost:987/resources/js/csdb4/app.js" type="module"> </script> --}}
  {{-- @vite(['resources/css/dmodule.css', 'resources/css/app.css', 'resources/css/loadingbar.css']) --}}
  {{-- @vite(['resources/css/app.css', 'resources/css/loadingbar.css']) --}}
  @vite(['resources/css/app.css'])
  @vite(['resources/js/csdb/app.js'])
  {{-- @vite(['resources/css/EditorDML.css']) --}}

  {{-- <script>
    if(window.Worker){
      window.w = new Worker(
        {{ 
          Vite::useBuildDirectory(env('VITE_BUILD_DIR', 'build'))
                  ->withEntryPoints(['resources/js/csdb4/worker.js'])
        }}
      )
    }
  </script> --}}
  
  {{-- @vite('resources/css/csdb.css') --}}
  
  {{-- <script type="text/javascript" src="/js/xmlvalidator/xmlvalidate.js-main/dist/worker.js"></script> --}}

  {{-- ga bisa pakai tooltips dari bootstrap, karena vue component akan dirender dynamic, sementara tooltip harus di initialize setelah component di render. Merepotkan sehingga tidak cocok --}}
  {{-- @vite('resources/scss/style.scss') --}}
  {{-- @vite('resources/js/bootstrap.js') --}}
  {{-- <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous"> --}}
  {{-- <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous"></script> --}}

  {{-- <link rel="stylesheet" href="https://www.jsdelivr.com/package/npm/@creativebulma/bulma-tooltip"> --}}
  {{-- <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@creativebulma/bulma-tooltip@1.2.0/dist/bulma-tooltip.min.css"> --}}
  {{-- @vite('resources/css/app2.css') --}}
  {{-- @vite('resources/scss/style.scss') --}}
  
  <title>CSDB</title>
</head>
<body id="body">
</body>