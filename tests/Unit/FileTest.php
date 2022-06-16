<?php

use AndreInocenti\LaravelFileS3Like\Services\File;
use Illuminate\Http\UploadedFile;

use function PHPUnit\Framework\assertTrue;

function toBase64($filepath)
{
    return base64_encode(file_get_contents($filepath));
}

test('test UploadedFile for txt file', function () {
    $filepath = TESTS_FILE_PATH . '/test-file.txt';
    $file = new UploadedFile($filepath, 'test.txt', 'text/plain', null, true);
    $file = new File($file, 'new-file.txt');
    assertTrue($file->getExtension() == 'txt');
    assertTrue($file->getFilename() == 'new-file.txt');
    assertTrue($file->getFile() == file_get_contents($filepath));
});

test('test UploadedFile for png file', function () {
    $filepath = TESTS_FILE_PATH . '/test-file.png';
    $file = new UploadedFile($filepath, 'test.png');
    $file = new File($file, 'new-file.png');
    assertTrue($file->getExtension() == 'png');
    assertTrue($file->getFilename() == 'new-file.png');
    assertTrue($file->getFile() == file_get_contents($filepath));
});

test('test base64 for txt file', function () {
    $filepath = TESTS_FILE_PATH . '/test-file.txt';
    $file = new File(toBase64($filepath), 'new-file');
    assertTrue($file->getExtension() == 'txt');
    assertTrue($file->getFilename() == 'new-file.txt');
    assertTrue($file->getFile() == file_get_contents($filepath));
});

test('test base64 for png image file', function () {
    $filepath = TESTS_FILE_PATH . '/test-file.png';
    $file = new File(toBase64($filepath), 'new-file');
    assertTrue($file->getExtension() == 'png');
    assertTrue($file->getFilename() == 'new-file.png');
    assertTrue($file->getFile() == file_get_contents($filepath));
});


test('test base64 for data uri text file', function () {
    $file = "data:@file/plain;base64,DQoNCkxvcmVtIGlwc3VtIGRvbG9yIHNpdCBhbWV0LCBjb25zZWN0ZXR1ciBhZGlwaXNjaW5nIGVsaXQuIFByb2luIGFjIGxpZ3VsYSB1bGxhbWNvcnBlciwgZmluaWJ1cyBkdWkgYXQsIGVsZW1lbnR1bSBzYXBpZW4uIEluIHNvZGFsZXMgcnV0cnVtIG1hdXJpcywgZXUgZWdlc3RhcyBtaSBncmF2aWRhIGEuIFByb2luIGNvbmRpbWVudHVtIGltcGVyZGlldCBsaWJlcm8gbmVjIHJob25jdXMuIEFsaXF1YW0gZHVpIGxvcmVtLCBjdXJzdXMgdWx0cmljZXMgc2NlbGVyaXNxdWUgbmVjLCBmaW5pYnVzIHNhZ2l0dGlzIHR1cnBpcy4gRG9uZWMgbWF4aW11cyBpZCBleCBhdCBmYWNpbGlzaXMuIENyYXMgZXN0IG5pc2ksIGVsZW1lbnR1bSBxdWlzIGlwc3VtIHF1aXMsIHBvc3VlcmUgZGlnbmlzc2ltIG51bGxhLiBTZWQgZXQgbWF4aW11cyBuaXNpLiBBbGlxdWFtIHNjZWxlcmlzcXVlIHB1cnVzIHNhcGllbiwgZWdldCBhbGlxdWV0IHJpc3VzIGxvYm9ydGlzIHZpdGFlLiBDbGFzcyBhcHRlbnQgdGFjaXRpIHNvY2lvc3F1IGFkIGxpdG9yYSB0b3JxdWVudCBwZXIgY29udWJpYSBub3N0cmEsIHBlciBpbmNlcHRvcyBoaW1lbmFlb3MuIFZpdmFtdXMgc2l0IGFtZXQgZmVsaXMgZmVsaXMuDQoNCkNsYXNzIGFwdGVudCB0YWNpdGkgc29jaW9zcXUgYWQgbGl0b3JhIHRvcnF1ZW50IHBlciBjb251YmlhIG5vc3RyYSwgcGVyIGluY2VwdG9zIGhpbWVuYWVvcy4gVmVzdGlidWx1bSBibGFuZGl0IHZ1bHB1dGF0ZSBvcmNpLCBlZ2V0IG1hbGVzdWFkYSBvcmNpIHNlbXBlciB1dC4gTnVuYyBzZW1wZXIgdm9sdXRwYXQgcG9ydHRpdG9yLiBDdXJhYml0dXIgc2NlbGVyaXNxdWUgbWV0dXMgc2l0IGFtZXQgbGlndWxhIHBsYWNlcmF0LCBldSBwZWxsZW50ZXNxdWUgcHVydXMgZmF1Y2lidXMuIFF1aXNxdWUgc2FnaXR0aXMgZXggdG9ydG9yLiBOdW5jIGV4IGVyYXQsIG1vbGxpcyBub24gZG9sb3Igbm9uLCBlbGVpZmVuZCBwcmV0aXVtIG5pc2wuIEN1cmFiaXR1ciBzY2VsZXJpc3F1ZSBhbnRlIHNpdCBhbWV0IGZpbmlidXMgcG9ydHRpdG9yLiBDcmFzIHByZXRpdW0gYW50ZSBuZWMgcHVydXMgdmVzdGlidWx1bSwgZWdldCBzb2RhbGVzIGVsaXQgaGVuZHJlcml0LiBTZWQgaW4gb3JjaSBhdCBtYXNzYSBpbXBlcmRpZXQgaGVuZHJlcml0IHV0IGV0IGxpZ3VsYS4NCg0KSW50ZWdlciB2ZWwgaW50ZXJkdW0gYXJjdS4gVXQgdGluY2lkdW50IGRpZ25pc3NpbSByaXN1cyBhIGxhb3JlZXQuIFN1c3BlbmRpc3NlIHBvdGVudGkuIFBoYXNlbGx1cyBleCBuZXF1ZSwgY29uZ3VlIHNlZCBsZW8gYSwgc29sbGljaXR1ZGluIHB1bHZpbmFyIGV4LiBEdWlzIGZldWdpYXQgbWFsZXN1YWRhIGxvcmVtLCBpZCBiaWJlbmR1bSBsYWN1cyBwb3J0YSBpbi4gRHVpcyBmZXVnaWF0IHRpbmNpZHVudCBtYXR0aXMuIEFsaXF1YW0gZXJhdCB2b2x1dHBhdC4gVml2YW11cyBjb25kaW1lbnR1bSB2ZWwgbnVsbGEgdml0YWUgaW1wZXJkaWV0LiBDdXJhYml0dXIgYWNjdW1zYW4gc2FnaXR0aXMgdmVuZW5hdGlzLiBEdWlzIHF1aXMgZG9sb3Igbm9uIHNhcGllbiB2dWxwdXRhdGUgbWF0dGlzIHV0IG1heGltdXMgbWF1cmlzLiBRdWlzcXVlIGVnZXQgbWFzc2Egb2Rpby4gU3VzcGVuZGlzc2UgbWFsZXN1YWRhIGVsZW1lbnR1bSBsYWN1cyBldCBtb2xsaXMuIFBlbGxlbnRlc3F1ZSBjb25kaW1lbnR1bSB0ZW1wb3IgbmlzaSwgaW4gZmF1Y2lidXMgbWkgYXVjdG9yIGVnZXQuIER1aXMgbW9sbGlzIGFyY3UgZHVpLg0KDQpVdCBub24gdHVycGlzIG5vbiBsZWN0dXMgdnVscHV0YXRlIGxvYm9ydGlzIHNlZCBpZCBvcmNpLiBOdW5jIGVnZXQgZmVsaXMgc2VkIG1hc3NhIGNvbmd1ZSB0aW5jaWR1bnQuIFNlZCBwZWxsZW50ZXNxdWUgdGVsbHVzIHZlbCBzZW0gYWxpcXVhbSwgZWdldCBzb2xsaWNpdHVkaW4gYW50ZSB1bHRyaWNpZXMuIFBoYXNlbGx1cyBwcmV0aXVtIHVybmEgZWdldCBhdWd1ZSBjb25kaW1lbnR1bSwgbmVjIHBlbGxlbnRlc3F1ZSBtYXVyaXMgcGVsbGVudGVzcXVlLiBFdGlhbSBldCBwb3J0YSBtYWduYS4gQ3JhcyBzZW1wZXIgbmVjIHR1cnBpcyBzaXQgYW1ldCBtYXR0aXMuIEFlbmVhbiBmYXVjaWJ1cyB2b2x1dHBhdCB0aW5jaWR1bnQuIE1vcmJpIGF1Y3RvciwgZG9sb3IgdXQgaWFjdWxpcyB0cmlzdGlxdWUsIGFyY3UgbGVjdHVzIGZyaW5naWxsYSB1cm5hLCBhYyBmZXVnaWF0IHNhcGllbiBuaXNpIGluIHZlbGl0LiBQcmFlc2VudCBzaXQgYW1ldCBkaWN0dW0gdmVsaXQsIGlkIGFjY3Vtc2FuIGRpYW0uIENyYXMgaWQgbGFjaW5pYSBmZWxpcy4gSW4gcXVpcyBjb252YWxsaXMgbGliZXJvLCBxdWlzIGV1aXNtb2Qgb2Rpby4gRHVpcyBlZ2V0IGVsaXQgYSBtZXR1cyBiaWJlbmR1bSBzZW1wZXIuIE1hdXJpcyB2b2x1dHBhdCBpYWN1bGlzIGVsZWlmZW5kLiBOdW5jIG5lYyBsaWJlcm8gdXJuYS4gU2VkIHV0IGVuaW0gYSB0b3J0b3IgZmV1Z2lhdCBzYWdpdHRpcyBuZWMgdml0YWUgaXBzdW0uIE51bmMgcGxhY2VyYXQsIGZlbGlzIGlkIGZldWdpYXQgcG9zdWVyZSwgZXN0IHB1cnVzIGZldWdpYXQgZXJhdCwgYmliZW5kdW0gcGhhcmV0cmEgZXN0IG9yY2kgYXQgYXJjdS4NCg0KTWF1cmlzIHZpdGFlIGx1Y3R1cyBtYXVyaXMuIE51bGxhIGJpYmVuZHVtLCBsb3JlbSBuZWMgdnVscHV0YXRlIHBsYWNlcmF0LCBhbnRlIGxhY3VzIG1hbGVzdWFkYSBuaXNpLCBub24gZGlnbmlzc2ltIGxlbyBlc3QgZWxlaWZlbmQgb3JjaS4gUHJhZXNlbnQgdm9sdXRwYXQgZXQgbG9yZW0gdmVsIGZyaW5naWxsYS4gRHVpcyBlZ2V0IHB1cnVzIHZpdGFlIG5pc2wgaWFjdWxpcyBzYWdpdHRpcyBlZ2V0IG5lYyBvZGlvLiBNYXVyaXMgbWF4aW11cyB2ZWwgZW5pbSBzZW1wZXIgdGVtcG9yLiBTZWQgdWx0cmljZXMgbG9yZW0gYXQgZGlhbSBwb3J0dGl0b3IgZGFwaWJ1cy4gTnVuYyBhbGlxdWFtIG5vbiBuaXNsIHV0IHZvbHV0cGF0LiBOdWxsYW0gdHJpc3RpcXVlIGZlcm1lbnR1bSByaXN1cy4g";
    $file = new File($file, 'new-file');
    assertTrue($file->getExtension() == 'txt');
    assertTrue($file->getFilename() == 'new-file.txt');
});