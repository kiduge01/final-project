<?php

declare(strict_types=1);

namespace App\Services;

final class SimplePdf
{
    private array $pages=[];
    private array $lines=[];
    private int $maxLines=48;

    public function line(string $text='', int $size=10, bool $bold=false): void
    {
        foreach ($this->wrap($text, $size) as $wrapped) {
            if (count($this->lines) >= $this->maxLines) $this->newPage();
            $this->lines[]=['text'=>$wrapped,'size'=>$size,'bold'=>$bold];
        }
    }

    public function gap(): void { $this->line(' ', 7); }

    public function newPage(): void
    {
        if ($this->lines) $this->pages[]=$this->lines;
        $this->lines=[];
    }

    public function output(string $filename='report.pdf'): void
    {
        $this->newPage();
        if (!$this->pages) $this->pages=[[]];
        $objects=[]; $pageIds=[]; $contentIds=[];
        $fontRegular=3; $fontBold=4;
        $objects[1]='<< /Type /Catalog /Pages 2 0 R >>';
        $objects[3]='<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>';
        $objects[4]='<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica-Bold >>';
        $next=5;
        foreach ($this->pages as $idx=>$page) {
            $contentId=$next++; $pageId=$next++;
            $content=$this->pageContent($page, $idx+1, count($this->pages));
            $objects[$contentId]="<< /Length ".strlen($content)." >>\nstream\n".$content."\nendstream";
            $objects[$pageId]='<< /Type /Page /Parent 2 0 R /MediaBox [0 0 595 842] /Resources << /Font << /F1 '.$fontRegular.' 0 R /F2 '.$fontBold.' 0 R >> >> /Contents '.$contentId.' 0 R >>';
            $pageIds[]=$pageId;
        }
        $objects[2]='<< /Type /Pages /Kids ['.implode(' ',array_map(fn($id)=>$id.' 0 R',$pageIds)).'] /Count '.count($pageIds).' >>';
        ksort($objects);
        $pdf="%PDF-1.4\n"; $offsets=[0];
        foreach ($objects as $id=>$body) { $offsets[$id]=strlen($pdf); $pdf.=$id." 0 obj\n".$body."\nendobj\n"; }
        $xref=strlen($pdf); $max=max(array_keys($objects));
        $pdf.="xref\n0 ".($max+1)."\n0000000000 65535 f \n";
        for($i=1;$i<=$max;$i++) $pdf.=sprintf('%010d 00000 n ', $offsets[$i]??0)."\n";
        $pdf.="trailer\n<< /Size ".($max+1)." /Root 1 0 R >>\nstartxref\n{$xref}\n%%EOF";
        while(ob_get_level()>0) ob_end_clean();
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="'.preg_replace('/[^A-Za-z0-9_.-]/','_',$filename).'"');
        header('Content-Length: '.strlen($pdf));
        echo $pdf; exit;
    }

    private function pageContent(array $lines, int $page, int $total): string
    {
        $y=790; $out="BT\n";
        foreach($lines as $line){ $font=$line['bold']?'F2':'F1'; $size=(int)$line['size']; $safe=$this->escape($line['text']); $out.="/{$font} {$size} Tf\n1 0 0 1 50 {$y} Tm\n({$safe}) Tj\n"; $y-=max(14,$size+5); }
        $out.="/F1 8 Tf\n1 0 0 1 50 28 Tm\n(Page {$page} of {$total}) Tj\nET";
        return $out;
    }

    private function wrap(string $text, int $size): array
    {
        $limit=max(45,(int)(1050/max(8,$size)));
        $text=preg_replace('/\s+/u',' ',trim($text)); if($text==='') return [''];
        return explode("\n",wordwrap($text,$limit,"\n",true));
    }
    private function escape(string $s): string { $s=str_replace(['\\','(',')'],['\\\\','\\(','\\)'],$s); return preg_replace('/[^\x20-\x7E]/','?', $s) ?? $s; }
}
