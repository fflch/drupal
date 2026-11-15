<?php

/**
 * @file
 * Baixa as traduções (.po) do core e de todos os projetos do composer.lock.
 *
 * Uso:
 *   php scripts/download-translations.php [DESTINO] [IDIOMA]
 *
 *   DESTINO  diretório onde salvar os arquivos (padrão: translations)
 *   IDIOMA   código do idioma (padrão: pt-br)
 *
 * Os arquivos ficam com o nome que o módulo locale espera
 * (%project-%version.%language.po). Para o Drupal usar só esses arquivos, sem
 * baixar nada do ftp.drupal.org, no settings.php:
 *
 *   $config['locale.settings']['translation']['path'] = 'DESTINO';
 *   $config['locale.settings']['translation']['use_source'] = 'local';
 *
 * Projetos sem tradução publicada (404) são ignorados. Sai com código 1 se a
 * tradução do core não puder ser baixada.
 */

$dir = rtrim($argv[1] ?? 'translations', '/');
$langcode = $argv[2] ?? 'pt-br';
$lock_file = __DIR__ . '/../composer.lock';
$server = 'https://ftp.drupal.org/files/translations/all';

if (!is_readable($lock_file)) {
  fwrite(STDERR, "composer.lock não encontrado em $lock_file\n");
  exit(1);
}
if (!is_dir($dir) && !mkdir($dir, 0775, TRUE)) {
  fwrite(STDERR, "Não foi possível criar o diretório $dir\n");
  exit(1);
}

// Projetos do drupal.org: o core e os pacotes com extra.drupal.version
// (preenchido pelo packages.drupal.org, ex.: "8.x-1.12").
$projects = [];
$lock = json_decode(file_get_contents($lock_file), TRUE);
foreach ($lock['packages'] as $package) {
  if ($package['name'] === 'drupal/core') {
    $projects['drupal'] = $package['version'];
  }
  elseif (strpos($package['name'], 'drupal/') === 0 && isset($package['extra']['drupal']['version'])) {
    $projects[substr($package['name'], strlen('drupal/'))] = $package['extra']['drupal']['version'];
  }
}

/**
 * Baixa uma URL. Retorna o conteúdo, NULL se não existir (404) ou FALSE se
 * falhar mesmo depois das novas tentativas.
 */
function download($url, $attempts = 3) {
  $context = stream_context_create([
    'http' => ['timeout' => 30, 'ignore_errors' => TRUE, 'user_agent' => 'drupal-translations-download'],
  ]);
  for ($i = 1; $i <= $attempts; $i++) {
    $body = @file_get_contents($url, FALSE, $context);
    // Último status da resposta (após redirecionamentos).
    $status = 0;
    foreach ($http_response_header ?? [] as $header) {
      if (preg_match('#^HTTP/\S+\s+(\d{3})#', $header, $m)) {
        $status = (int) $m[1];
      }
    }
    if ($status === 200 && $body !== FALSE) {
      return $body;
    }
    if ($status === 404) {
      return NULL;
    }
    sleep($i);
  }
  return FALSE;
}

$ok = $missing = $failed = [];
foreach ($projects as $project => $version) {
  $file = "$project-$version.$langcode.po";
  $po = download("$server/$project/$file");
  if (is_string($po)) {
    file_put_contents("$dir/$file", $po);
    $ok[] = $file;
  }
  elseif ($po === NULL) {
    $missing[] = $file;
  }
  else {
    $failed[] = $file;
  }
}

printf("%d baixados, %d sem tradução publicada, %d com falha (em %s)\n", count($ok), count($missing), count($failed), $dir);
if ($missing) {
  echo "Sem tradução publicada: " . implode(', ', $missing) . "\n";
}
if ($failed) {
  fwrite(STDERR, "Falha ao baixar: " . implode(', ', $failed) . "\n");
}

if (!in_array("drupal-{$projects['drupal']}.$langcode.po", $ok, TRUE)) {
  fwrite(STDERR, "A tradução do core não foi baixada.\n");
  exit(1);
}
