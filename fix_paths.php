<?php
$dir = __DIR__;

function replaceInDir($dir) {
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
    foreach ($iterator as $file) {
        if ($file->isFile()) {
            $ext = $file->getExtension();
            if (in_array($ext, ['js', 'php', 'html', 'css', 'txt'])) {
                $content = file_get_contents($file->getPathname());
                
                // Replace /hisabat.in with /hisabat.in
                $newContent = str_replace('/hisabat.in', '/hisabat.in', $content);
                
                // Fix ui/index.html absolute asset paths
                if ($file->getFilename() === 'index.html' || $file->getFilename() === '.htaccess') {
                    $newContent = str_replace('src="/assets/', 'src="/hisabat.in/assets/', $newContent);
                    $newContent = str_replace('href="/assets/', 'href="/hisabat.in/assets/', $newContent);
                    $newContent = str_replace('href="/vite.svg"', 'href="/hisabat.in/vite.svg"', $newContent);
                }

                if ($content !== $newContent) {
                    file_put_contents($file->getPathname(), $newContent);
                    echo "Updated: " . $file->getPathname() . "\n";
                }
            }
        }
    }
}

replaceInDir($dir);
echo "Done.\n";
?>
