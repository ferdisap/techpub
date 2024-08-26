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
    <p>New comment has been added to following CSDB Object:
      <ol>
        @foreach ($COMMENTModel->commentRefs as $ref)
        <li>{{ $ref }}</li>
        @endforeach
      </ol>
    </p>
    <p>From: {{ $COMMENTModel->csdb->owner->first_name }}</p>
    @foreach ($COMMENTModel->commentContent as $para)
    <p>{{ $para }}</p>
    @endforeach
    <p>Touch in the comment by click the link. Thankyou</p>
  </div>
  <div style="font-style:italic">Technical Publication Department</div>
</body>
</html>