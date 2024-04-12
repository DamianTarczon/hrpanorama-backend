<?php

require 'vendor/autoload.php';

use Services\AuthService;
use Services\PDFService;
use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();
$requestMethod = $_SERVER['REQUEST_METHOD'];
$uri = $_SERVER['REQUEST_URI'];

setHeaders();

handleCorsPreflight($requestMethod);

handleRequest($uri, $requestMethod);

function setHeaders(): void
{
  header('Content-Type: application/json');
  header('Access-Control-Allow-Origin: *');
  header('Access-Control-Allow-Methods: POST');
  header('Access-Control-Allow-Headers: Content-Type, Authorization');
}

function handleCorsPreflight($requestMethod): void
{
  if ($requestMethod === 'OPTIONS') {
    header('Access-Control-Allow-Methods: POST, OPTIONS');
    http_response_code(200);
    exit;
  }
}

function handleRequest($uri, $requestMethod): void
{
  switch ($uri) {
    case '/get-pdf':
      if ($requestMethod === 'POST') {
        processPostRequest();
      } else {
        http_response_code(405);
        header('Allow: POST');
        echo json_encode(['error' => 'Method Not Allowed. Only POST requests are allowed.']);
      }
      break;
    default:
      http_response_code(404);
      echo json_encode(['error' => 'Not Found']);
      break;
  }
}

function processPostRequest(): void
{
  $authService = new AuthService();
  $token = $authService->getBearerTokenFromHeader(getallheaders());

  if (!$authService->validateToken($token)) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized: Invalid token.']);
    exit;
  }

  $data = json_decode(file_get_contents('php://input'), true);
  $text = $data['text'] ?? 'Default Text';

  $pdfService = new PDFService();
  $pdfFilePath = $pdfService->createPDFWithQRCode($text, 'https://hrpanorama.pl/');

  if (!$pdfFilePath) {
    http_response_code(500);
    echo json_encode(['error' => 'Internal Server Error: Failed to generate PDF.']);
    exit;
  }

  respondWithPDFPath($pdfFilePath);
}

function respondWithPDFPath($pdfFilePath): void
{
  $baseUrl = $_ENV['BASE_URL'] ?? '';
  $pdfUrlPath = str_replace(__DIR__, '', $pdfFilePath);
  $pdfUrl = $baseUrl . ltrim($pdfUrlPath, '/');

  echo json_encode(['url' => $pdfUrl]);
}
