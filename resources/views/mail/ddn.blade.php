<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="ie=edge">
</head>
<body style="font-family:Verdana, Geneva, Tahoma, sans-serif">
  <div>Dear Mr. / Ms.</div>
  <div>
    <p>Dispatch note of {{ $DDNModel->csdb->filename }} has been created by {{ $DDNModel->csdb->owner->name() }}. The dispatch deliveried items are:
    <ul>
      @foreach ($DDNModel->ddnContent as $item)
      <li>{{ $item }}</li>
      @endforeach
    </ul>
    </p>
    <p>Please review by creating comment on that link. No need to reply this email. Thankyou</p>
  </div>
  <div style="font-style:italic">Technical Publication Department</div>
</body>
</html>