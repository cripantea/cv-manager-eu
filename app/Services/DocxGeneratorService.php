<?php

namespace App\Services;

use App\Models\Cv;
use App\Models\Project;
use Carbon\Carbon;
use PhpOffice\PhpWord\Element\Section;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\Settings;
use PhpOffice\PhpWord\SimpleType\Jc;

class DocxGeneratorService
{
    // Front page column widths
    private const LEFT_W  = 3770;
    private const RIGHT_W = 7002;
    private const DBL     = 6;
    private const SGL     = 4;

    // Expertise + experience page widths (matching template exactly)
    private const FULL_W  = 10740;
    private const EXP_L   = 2428;
    private const EXP_R   = 8312;
    private const EXPT_W  = [534, 2409, 1276, 1418, 5103];
    private const TRAIN_W = [534, 1894, 1894, 2307, 4111];

    // Border sizes for expertise/experience pages (eighths of a point, matching template)
    private const T_DBL   = 4;   // double border
    private const T_THICK = 12;  // thick single border
    private const T_THIN  = 6;   // thin single border

    private const FONT       = ['name' => 'Arial', 'size' => 10];
    private const FONT_B     = ['name' => 'Arial', 'size' => 10, 'bold' => true];
    private const FONT_L     = ['name' => 'Arial', 'size' => 16, 'bold' => true];
    private const PARA       = ['spaceAfter' => 120, 'spaceBefore' => 120, 'spaceLeft' => 120];
    private const PARA_TIGHT = ['spaceAfter' => 0,   'spaceBefore' => 0,   'spaceLeft' => 60];

    public function __construct(private ExpertiseCalculatorService $expertiseCalc) {}

    public function generate(Cv $cv): string
    {
        Settings::setOutputEscapingEnabled(true);

        $cv->loadMissing(['user', 'projects.technologies', 'educations', 'trainings']);

        $phpWord = new PhpWord();
        $phpWord->setDefaultFontName('Arial');
        $phpWord->setDefaultFontSize(10);

        $section = $phpWord->addSection([
            'pageWidth'    => 11906,
            'pageHeight'   => 16838,
            'marginTop'    => 720,
            'marginBottom' => 720,
            'marginLeft'   => 720,
            'marginRight'  => 720,
        ]);

        $this->addFrontPage($section, $cv);
        $this->addSummaryPage($section, $cv);
        $this->addTrainingPage($section, $cv);
        $this->addExpertisePage($section, $cv);
        $this->addProjectPages($section, $cv);

        $tmpFile = tempnam(sys_get_temp_dir(), 'cv_') . '.docx';
        IOFactory::createWriter($phpWord, 'Word2007')->save($tmpFile);

        return $tmpFile;
    }

    // ─── Front page ──────────────────────────────────────────────────────────

    private function addFrontPage(Section $section, Cv $cv): void
    {
        $section->addHeader()->addText(
            'DIGIT-TM II – CV Form for Time & Means services',
            ['name' => 'Arial', 'size' => 10, 'italic' => true],
            ['alignment' => Jc::CENTER, 'spaceBefore' => 0, 'spaceAfter' => 0]
        );

        $section->addText(
            'CV FRONT PAGE',
            self::FONT_B,
            ['alignment' => Jc::START, 'spaceBefore' => 0, 'spaceAfter' => 160]
        );

        $rows  = $this->buildRows($cv);
        $total = count($rows);
        $table = $section->addTable();

        foreach ($rows as $i => [$label, $content]) {
            $isFirst = $i === 0;
            $isLast  = $i === $total - 1;

            $table->addRow();

            $lCell = $table->addCell(self::LEFT_W, $this->cellStyle(true, $isFirst, $isLast));
            $lCell->addText($label, self::FONT_B, self::PARA);

            $rCell = $table->addCell(self::RIGHT_W, $this->cellStyle(false, $isFirst, $isLast));
            $type = is_array($content) ? ($content[0] ?? null) : null;
            if ($type === '__2col__') {
                $nb = $this->noBorder();
                $w1 = $content[3] ?? (int)(self::RIGHT_W / 2);
                $w2 = $content[4] ?? (self::RIGHT_W - $w1);
                $nested = $rCell->addTable();
                $nested->addRow();
                $c1 = $nested->addCell($w1, $nb);
                $lines1 = (array) $content[1];
                foreach ($lines1 ?: [''] as $line) {
                    $c1->addText((string) $line, self::FONT, self::PARA_TIGHT);
                }
                $c2 = $nested->addCell($w2, $nb);
                $lines2 = (array) $content[2];
                foreach ($lines2 ?: [''] as $line) {
                    $c2->addText((string) $line, self::FONT, self::PARA_TIGHT);
                }
            } elseif ($type === '__3col__') {
                $nb = $this->noBorder();
                $w1 = $content[4] ?? (int)(self::RIGHT_W / 3);
                $w2 = $content[5] ?? (int)(self::RIGHT_W / 3);
                $w3 = $content[6] ?? (self::RIGHT_W - $w1 - $w2);
                $nested = $rCell->addTable();
                $nested->addRow();
                $c1 = $nested->addCell($w1, $nb);
                foreach ((array) $content[1] ?: [''] as $line) {
                    $c1->addText((string) $line, self::FONT, self::PARA_TIGHT);
                }
                $c2 = $nested->addCell($w2, $nb);
                foreach ((array) $content[2] ?: [''] as $line) {
                    $c2->addText((string) $line, self::FONT, self::PARA_TIGHT);
                }
                $c3 = $nested->addCell($w3, $nb);
                foreach ((array) $content[3] ?: [''] as $line) {
                    $c3->addText((string) $line, self::FONT, self::PARA_TIGHT);
                }
            } elseif ($type === '__lang_table__') {
                $langs = $content[1] ?? [];
                $w1 = $content[2] ?? 2000;
                $w2 = $content[3] ?? 2000;
                $w3 = $content[4] ?? (self::RIGHT_W - $w1 - $w2);
                $nb = $this->noBorder();
                $nested = $rCell->addTable();
                $nested->addRow();
                $nested->addCell($w1, $nb)->addText('Language', self::FONT_B, self::PARA_TIGHT);
                $nested->addCell($w2, $nb)->addText('Spoken', self::FONT_B, self::PARA_TIGHT);
                $nested->addCell($w3, $nb)->addText('Written', self::FONT_B, self::PARA_TIGHT);
                $langRows = !empty($langs) ? $langs : [['name' => '', 'spoken' => '', 'written' => '']];
                foreach ($langRows as $lang) {
                    $nested->addRow();
                    $nested->addCell($w1, $nb)->addText($lang['name'] ?? '', self::FONT, self::PARA_TIGHT);
                    $nested->addCell($w2, $nb)->addText($lang['spoken'] ?? '', self::FONT, self::PARA_TIGHT);
                    $nested->addCell($w3, $nb)->addText($lang['written'] ?? '', self::FONT, self::PARA_TIGHT);
                }
            } else {
                foreach ((array) $content as $line) {
                    $rCell->addText((string) $line, self::FONT, self::PARA);
                }
            }
        }
    }

    private function buildRows(Cv $cv): array
    {
        $rows = [];

        $rows[] = [
            'Surname, first name :',
            trim(ucfirst($cv->last_name ?? '') . ', ' . ucfirst($cv->first_name ?? ''), ', '),
        ];

        $rows[] = ['Date of last update :', Carbon::now()->format('d/m/Y')];

        $rows[] = ['e-mail address:', $cv->user->email ?? ''];

        $rows[] = [
            'External service provider information:',
            ['__2col__',
                'Date of birth: ' . $this->fmt($cv->birth_date),
                'Nationality: ' . ($cv->nationality ?? ''),
                2534, 4468,   // ~36% / ~64% of RIGHT_W
            ],
        ];

        $contractType = $cv->contract_type ?? '';
        $contractLeft = [
            ($contractType === 'permanent'     ? '[x]' : '[ ]') . ' Permanent employee',
            ($contractType === 'non-permanent' ? '[x]' : '[ ]') . ' Non-permanent employee',
            ($contractType === 'freelancer'    ? '[x]' : '[ ]') . ' Freelancer / self-employed',
            '',
            'Employer: Unisystems',
        ];
        $contractRight = [
            'Date of recruitment: at contract start',
            'Number of months working for the tenderer: 0 months',
            'Comments: ',
        ];
        $rows[] = ['Type of contract:', ['__2col__', $contractLeft, $contractRight, 3400, 3602]];

        $rows[] = ['Current function :', $cv->current_function ?? ''];

        $rows[] = [
            'Profile for which external service provider is entered:',
            ['__2col__',
                $cv->profile_for ?? '',
                $cv->date_available ? 'Date available: ' . $this->fmt($cv->date_available) : '',
                2897, 4105,   // ~41% / ~59% of RIGHT_W
            ],
        ];

        $lvl = $cv->education_level;
        $col1 = [
            ($lvl === 'master'    ? '[x]' : '[ ]') . ' Master degree or equivalent (≥4 years)',
            ($lvl === 'bachelor'  ? '[x]' : '[ ]') . ' Bachelor degree or equivalent (≥3 years)',
            ($lvl === 'secondary' ? '[x]' : '[ ]') . ' Secondary school',
        ];
        $col2 = [];
        $col3 = [];
        foreach ($cv->educations->sortBy('order') as $edu) {
            $col2[] = ($edu->certificate_diploma ?? '');
            $col2[] = ($edu->institute ?? '');
            $col2[] = '';
            if ($edu->start_date) {
                $col3[] = 'Start: ' . $edu->start_date;
                $col3[] = 'End: '   . ($edu->end_date ?? '');
                $col3[] = '';
            } else {
                $col3[] = 'End: ' . ($edu->end_date ?? '');
                $col3[] = '';
                $col3[] = '';
            }
        }
        if (empty($col2)) {
            $col2 = [''];
            $col3 = [''];
        }
        $rows[] = ['Highest relevant educational qualification:',
            ['__3col__', $col1, $col2, $col3, 2400, 2800, 1802]];

        $langs = $cv->languages ?? [];
        $rows[] = ['Languages:', ['__lang_table__', $langs, 2000, 2000, 3002]];

        $careerStartStr = 'Date IT career started: ' . $this->fmt($cv->it_career_start, 'm/Y');
        if ($cv->it_career_start) {
            $monthsTotal = (int) $cv->it_career_start->diffInMonths(Carbon::now());
            $years = intdiv($monthsTotal, 12);
            $expStr = 'Number of years/months of experience (apart from the studies): '
                . $monthsTotal . ' months / ' . $years . ' years';
        } else {
            $expStr = 'Number of years/months of experience (apart from the studies): ';
        }
        $rows[] = ['Professional experience:', ['__2col__', $careerStartStr, $expStr, 3200, 3802]];

        $expertise = $this->expertiseCalc->calculate($cv);
        arsort($expertise);
        $expertiseStr = implode('; ', array_map(
            fn($tech, $months) => ucfirst($tech) . ': ' . $months . ' months',
            array_keys($expertise),
            array_values($expertise)
        ));
        $rows[] = ['Specific expertise(s) (with number of months experience for each):', $expertiseStr ?: ''];

        $rows[] = ['Standard(s) or certificate(s):', $cv->standards_certificates ?? ''];

        $rows[] = ['Proposed level:', $cv->proposed_level ?? ''];

        return $rows;
    }

    private function cellStyle(bool $isLeft, bool $isFirst, bool $isLast, string $bgColor = 'FFFFFF'): array
    {
        return [
            'bgColor'           => $bgColor,
            'vAlign'            => 'top',
            'cellMarginLeft'    => 120,
            'cellMarginRight'   => 100,
            'cellMarginTop'     => 120,
            'cellMarginBottom'  => 120,
            'borderTopStyle'    => $isFirst ? 'double' : 'single',
            'borderTopSize'     => $isFirst ? self::DBL : self::SGL,
            'borderTopColor'    => '000000',
            'borderBottomStyle' => $isLast ? 'double' : 'single',
            'borderBottomSize'  => $isLast ? self::DBL : self::SGL,
            'borderBottomColor' => '000000',
            'borderLeftStyle'   => $isLeft ? 'double' : 'single',
            'borderLeftSize'    => $isLeft ? self::DBL : self::SGL,
            'borderLeftColor'   => '000000',
            'borderRightStyle'  => !$isLeft ? 'double' : 'single',
            'borderRightSize'   => !$isLeft ? self::DBL : self::SGL,
            'borderRightColor'  => '000000',
        ];
    }

    // ─── CV Summary page ──────────────────────────────────────────────────────

    private function addSummaryPage(Section $section, Cv $cv): void
    {
        $section->addPageBreak();

        $section->addText(
            'CV Summary',
            self::FONT_B,
            ['alignment' => Jc::START, 'spaceBefore' => 0, 'spaceAfter' => 120]
        );

        $cellStyle = [
            'vAlign'            => 'top',
            'cellMarginLeft'    => 120, 'cellMarginRight'   => 120,
            'cellMarginTop'     => 120, 'cellMarginBottom'  => 120,
            'borderTopStyle'    => 'double', 'borderTopSize'    => self::T_DBL, 'borderTopColor'    => 'auto',
            'borderBottomStyle' => 'double', 'borderBottomSize' => self::T_DBL, 'borderBottomColor' => 'auto',
            'borderLeftStyle'   => 'double', 'borderLeftSize'   => self::T_DBL, 'borderLeftColor'   => 'auto',
            'borderRightStyle'  => 'double', 'borderRightSize'  => self::T_DBL, 'borderRightColor'  => 'auto',
        ];

        $table = $section->addTable();
        $table->addRow();
        $cell = $table->addCell(10598, $cellStyle);

        $summary = trim($cv->profile_summary ?? '');
        if ($summary !== '') {
            foreach (explode("\n", $summary) as $line) {
                $cell->addText($line, self::FONT, ['spaceAfter' => 120]);
            }
        } else {
            $cell->addText('', self::FONT, ['spaceAfter' => 120]);
        }
    }

    // ─── Training page ────────────────────────────────────────────────────────

    private function addTrainingPage(Section $section, Cv $cv): void
    {
        $section->addPageBreak();

        $this->addNumberTable($section, 'CV training page number for this CV:', 1, 3794, 6946);

        $colW     = self::TRAIN_W;
        $trainings = $cv->trainings->sortBy('order')->values();
        $rows      = $trainings->isNotEmpty() ? $trainings : collect([null]); // at least one empty row
        $lastIdx   = count($rows) - 1;

        $table = $section->addTable();

        // Row 0: "TRAINING" full-span header — double top/left/right, no bottom
        $table->addRow();
        $table->addCell(self::FULL_W, [
            'gridSpan'       => 5,
            'vAlign'         => 'center',
            'cellMarginLeft' => 120, 'cellMarginRight'  => 120,
            'cellMarginTop'  => 120, 'cellMarginBottom' => 120,
            'borderTopStyle'   => 'double', 'borderTopSize'   => self::T_DBL, 'borderTopColor'   => 'auto',
            'borderLeftStyle'  => 'double', 'borderLeftSize'  => self::T_DBL, 'borderLeftColor'  => 'auto',
            'borderRightStyle' => 'double', 'borderRightSize' => self::T_DBL, 'borderRightColor' => 'auto',
        ])->addText('TRAINING', self::FONT_B, ['alignment' => Jc::CENTER, 'spaceBefore' => 120, 'spaceAfter' => 120]);

        // Row 1: column headers
        $headers = [
            '#',
            'Training name:',
            'Company/institute organising the training:',
            'Date(s) training followed:',
            'Exams or certificates:',
        ];
        $table->addRow();
        foreach ($headers as $ci => $hdr) {
            $table->addCell($colW[$ci], $this->exptCellStyle($ci, false))
                  ->addText($hdr, self::FONT_B, self::PARA);
        }

        // Data rows
        foreach ($rows as $i => $training) {
            $isLast = ($i === $lastIdx);
            $table->addRow();
            $values = [
                $training ? ($i + 1) : '',
                $training?->training_name      ?? '',
                $training?->company_institute  ?? '',
                $training ? $this->fmt($training->date_followed) : '',
                $training?->certificate_obtained ?? '',
            ];
            foreach ($values as $ci => $val) {
                $table->addCell($colW[$ci], $this->exptCellStyle($ci, $isLast))
                      ->addText((string) $val, self::FONT, self::PARA);
            }
        }
    }

    // ─── Expertise page ───────────────────────────────────────────────────────

    private function addExpertisePage(Section $section, Cv $cv): void
    {
        $section->addPageBreak();

        $this->addNumberTable($section, 'CV software expertise page number for this CV:', 1, 4503, 6237);

        // Collect per-tech data: max competence + project references
        $projects = $cv->projects->sortBy('order')->values();
        $techData = [];
        foreach ($projects as $idx => $project) {
            $n = $idx + 1;
            foreach ($project->technologies as $tech) {
                $key = strtolower(trim($tech->technology_name));
                if (!isset($techData[$key])) {
                    $techData[$key] = ['name' => $tech->technology_name, 'competence' => null, 'projects' => []];
                }
                $comp = isset($tech->competence) ? (int) $tech->competence : null;
                if ($comp !== null) {
                    $techData[$key]['competence'] = max($techData[$key]['competence'] ?? 0, $comp);
                }
                if (!in_array($n, $techData[$key]['projects'], true)) {
                    $techData[$key]['projects'][] = $n;
                }
            }
        }

        $expertise = $this->expertiseCalc->calculate($cv);
        arsort($expertise);

        $colW      = self::EXPT_W;
        $techKeys  = array_keys($expertise);
        $lastIdx   = count($techKeys) - 1;

        $table = $section->addTable();

        // Row 0: "Software expertise" full-span header — double top/left/right, no bottom
        $table->addRow();
        $table->addCell(self::FULL_W, [
            'gridSpan'          => 5,
            'vAlign'            => 'center',
            'cellMarginLeft'    => 120, 'cellMarginRight'   => 120,
            'cellMarginTop'     => 120, 'cellMarginBottom'  => 120,
            'borderTopStyle'    => 'double', 'borderTopSize'    => self::T_DBL, 'borderTopColor'    => 'auto',
            'borderLeftStyle'   => 'double', 'borderLeftSize'   => self::T_DBL, 'borderLeftColor'   => 'auto',
            'borderRightStyle'  => 'double', 'borderRightSize'  => self::T_DBL, 'borderRightColor'  => 'auto',
        ])->addText('Software expertise', self::FONT_B, ['alignment' => Jc::CENTER, 'spaceBefore' => 120, 'spaceAfter' => 120]);

        // Row 1: column header labels
        $headers = [
            '#',
            'Tool (when possible precise manufacturer, product name and version(s))',
            'Competence (rating : 1 -5)',
            'Duration (in months)',
            'Description (reference to relevant entries under "professional experience" is mandatory)',
        ];
        $table->addRow();
        foreach ($headers as $ci => $hdr) {
            $table->addCell($colW[$ci], $this->exptCellStyle($ci, false))
                  ->addText($hdr, self::FONT_B, self::PARA);
        }

        // Data rows
        foreach ($techKeys as $i => $techKey) {
            $months   = $expertise[$techKey];
            $info     = $techData[$techKey] ?? null;
            $isLast   = ($i === $lastIdx);
            $projRefs = $info && !empty($info['projects'])
                ? 'Project ' . implode(', ', $info['projects'])
                : '';

            $table->addRow();
            $values = [
                $i + 1,
                $info ? $info['name'] : ucfirst($techKey),
                $info && $info['competence'] !== null ? (string) $info['competence'] : '',
                $months,
                $projRefs,
            ];
            foreach ($values as $ci => $val) {
                $table->addCell($colW[$ci], $this->exptCellStyle($ci, $isLast))
                      ->addText((string) $val, self::FONT, self::PARA);
            }
        }
    }

    // Cell style for expertise table data/header rows
    private function exptCellStyle(int $col, bool $isLast): array
    {
        return [
            'bgColor'           => 'FFFFFF',
            'vAlign'            => 'top',
            'cellMarginLeft'    => 80,  'cellMarginRight'   => 80,
            'cellMarginTop'     => 80,  'cellMarginBottom'  => 80,
            'borderTopStyle'    => 'single', 'borderTopSize'    => self::T_THICK, 'borderTopColor'    => 'auto',
            'borderBottomStyle' => $isLast ? 'double' : 'single',
            'borderBottomSize'  => $isLast ? self::T_DBL : self::T_THICK,
            'borderBottomColor' => 'auto',
            'borderLeftStyle'   => $col === 0 ? 'double' : 'single',
            'borderLeftSize'    => $col === 0 ? self::T_DBL : self::T_THIN,
            'borderLeftColor'   => 'auto',
            'borderRightStyle'  => $col === 4 ? 'double' : 'single',
            'borderRightSize'   => $col === 4 ? self::T_DBL : self::T_THIN,
            'borderRightColor'  => 'auto',
        ];
    }

    // ─── Experience pages ─────────────────────────────────────────────────────

    private function addProjectPages(Section $section, Cv $cv): void
    {
        foreach ($cv->projects->sortBy('order')->values() as $projectIndex => $project) {
            $section->addPageBreak();

            $this->addNumberTable($section, 'CV experience page number for this CV:', $projectIndex + 1, 3794, 6946);

            $table = $section->addTable();

            // Row 0: "PROJECT EXPERIENCE" full-span header
            $table->addRow();
            $table->addCell(self::FULL_W, [
                'gridSpan'          => 2,
                'vAlign'            => 'center',
                'cellMarginLeft'    => 120, 'cellMarginRight'   => 120,
                'cellMarginTop'     => 120, 'cellMarginBottom'  => 120,
                'borderTopStyle'    => 'double', 'borderTopSize'    => self::T_DBL,   'borderTopColor'    => 'auto',
                'borderBottomStyle' => 'single', 'borderBottomSize' => self::T_THICK, 'borderBottomColor' => 'auto',
                'borderLeftStyle'   => 'double', 'borderLeftSize'   => self::T_DBL,   'borderLeftColor'   => 'auto',
                'borderRightStyle'  => 'double', 'borderRightSize'  => self::T_DBL,   'borderRightColor'  => 'auto',
            ])->addText('PROJECT EXPERIENCE', self::FONT_B, ['alignment' => Jc::CENTER, 'spaceBefore' => 120, 'spaceAfter' => 120]);

            // Data rows (1-based position within the 8 data rows)
            $rows = $this->buildProjectRows($project);
            foreach ($rows as $i => $row) {
                $table->addRow();
                $pos    = $i + 1; // table row index (1–8)
                $isLast = ($i === count($rows) - 1);

                if ($row['full_width']) {
                    $cell = $table->addCell(self::FULL_W, $this->expFullStyle($pos, $isLast));
                    $cell->addText($row['label'], self::FONT_B, self::PARA);
                    foreach ((array) $row['content'] as $line) {
                        $cell->addText((string) $line, self::FONT, self::PARA);
                    }
                } else {
                    $lCell = $table->addCell(self::EXP_L, $this->expLeftStyle($pos));
                    // Dates row: split label into two paragraphs matching template
                    if ($row['split_label'] ?? false) {
                        foreach ($row['label'] as $labelLine) {
                            $lCell->addText($labelLine, self::FONT_B, self::PARA);
                        }
                    } else {
                        $lCell->addText($row['label'], self::FONT_B, self::PARA);
                    }

                    $rCell = $table->addCell(self::EXP_R, $this->expRightStyle($pos));
                    foreach ((array) $row['content'] as $line) {
                        $rCell->addText((string) $line, self::FONT, self::PARA);
                    }
                }
            }
        }
    }

    // Left cell style for 2-col experience rows (pos 1–5)
    private function expLeftStyle(int $pos): array
    {
        [$topSz, $botSz] = $this->expRowBorders($pos);
        return [
            'bgColor'           => 'FFFFFF',
            'vAlign'            => 'top',
            'cellMarginLeft'    => 120, 'cellMarginRight'   => 100,
            'cellMarginTop'     => 120, 'cellMarginBottom'  => 120,
            'borderTopStyle'    => 'single', 'borderTopSize'    => $topSz, 'borderTopColor'    => 'auto',
            'borderBottomStyle' => 'single', 'borderBottomSize' => $botSz, 'borderBottomColor' => 'auto',
            'borderLeftStyle'   => 'double', 'borderLeftSize'   => self::T_DBL,  'borderLeftColor'   => 'auto',
            'borderRightStyle'  => 'single', 'borderRightSize'  => self::T_THIN, 'borderRightColor'  => 'auto',
        ];
    }

    // Right cell style for 2-col experience rows (pos 1–5)
    private function expRightStyle(int $pos): array
    {
        [$topSz, $botSz] = $this->expRowBorders($pos);
        return [
            'bgColor'           => 'FFFFFF',
            'vAlign'            => 'top',
            'cellMarginLeft'    => 120, 'cellMarginRight'   => 100,
            'cellMarginTop'     => 120, 'cellMarginBottom'  => 120,
            'borderTopStyle'    => 'single', 'borderTopSize'    => $topSz, 'borderTopColor'    => 'auto',
            'borderBottomStyle' => 'single', 'borderBottomSize' => $botSz, 'borderBottomColor' => 'auto',
            'borderLeftStyle'   => 'single', 'borderLeftSize'   => self::T_THIN, 'borderLeftColor'   => 'auto',
            'borderRightStyle'  => 'double', 'borderRightSize'  => self::T_DBL,  'borderRightColor'  => 'auto',
        ];
    }

    // Full-span cell style for experience rows (pos 6–8)
    private function expFullStyle(int $pos, bool $isLast): array
    {
        $botStyle = $isLast ? 'double' : 'single';
        $botSz    = $isLast ? self::T_DBL : self::T_THICK;
        return [
            'gridSpan'          => 2,
            'bgColor'           => 'FFFFFF',
            'vAlign'            => 'top',
            'cellMarginLeft'    => 120, 'cellMarginRight'   => 100,
            'cellMarginTop'     => 120, 'cellMarginBottom'  => 120,
            'borderTopStyle'    => 'single', 'borderTopSize'    => self::T_THICK, 'borderTopColor'    => 'auto',
            'borderBottomStyle' => $botStyle, 'borderBottomSize' => $botSz,      'borderBottomColor' => 'auto',
            'borderLeftStyle'   => 'double', 'borderLeftSize'   => self::T_DBL,  'borderLeftColor'   => 'auto',
            'borderRightStyle'  => 'double', 'borderRightSize'  => self::T_DBL,  'borderRightColor'  => 'auto',
        ];
    }

    // Returns [topSize, bottomSize] for experience 2-col rows based on position
    private function expRowBorders(int $pos): array
    {
        // Rows 1-2 (Project name, Company): thick top, thin bottom
        // Rows 3-5 (Dates, Client, Project size): thin top and bottom
        return match (true) {
            $pos <= 2 => [self::T_THICK, self::T_THIN],
            default   => [self::T_THIN,  self::T_THIN],
        };
    }

    private function buildProjectRows(Project $project): array
    {
        $startFmt = $this->fmt($project->start_date);
        $endFmt   = $project->end_date ? $this->fmt($project->end_date) : 'Ongoing';
        $months   = $project->effectiveMonths();

        $techList = $project->technologies->pluck('technology_name')->implode(', ');

        $bullets = array_map(
            fn($item) => '• ' . $item,
            array_filter(array_merge($project->roles ?? [], $project->responsibilities ?? []))
        );

        return [
            ['label' => 'Project name:',        'content' => $project->project_name ?? '', 'full_width' => false],
            ['label' => 'Company (employer):',  'content' => $project->employer ?? '',     'full_width' => false],
            [
                'label'       => ['Dates (start-end):', 'Effective number of months achieved:'],
                'content'     => [$startFmt . ' — ' . $endFmt, $months . ' months'],
                'full_width'  => false,
                'split_label' => true,
            ],
            ['label' => 'Client (customer) :',  'content' => $project->client ?? '',       'full_width' => false],
            ['label' => 'Project size:',         'content' => $project->project_size ?? '', 'full_width' => false],
            ['label' => 'Project description:',  'content' => $project->description ?? '',  'full_width' => true],
            [
                'label'      => "External service provider's roles & responsibilities in the project:",
                'content'    => !empty($bullets) ? $bullets : [''],
                'full_width' => true,
            ],
            [
                'label'      => 'Technologies and methodologies used by the external service provider in the project:',
                'content'    => $techList,
                'full_width' => true,
            ],
        ];
    }

    // ─── Shared helpers ───────────────────────────────────────────────────────

    private function noBorder(): array
    {
        return [
            'borderTopSize'    => 0, 'borderTopColor'    => 'FFFFFF',
            'borderBottomSize' => 0, 'borderBottomColor' => 'FFFFFF',
            'borderLeftSize'   => 0, 'borderLeftColor'   => 'FFFFFF',
            'borderRightSize'  => 0, 'borderRightColor'  => 'FFFFFF',
            'cellMarginTop'    => 0, 'cellMarginBottom'  => 0,
            'cellMarginLeft'   => 0, 'cellMarginRight'   => 0,
        ];
    }

    // 2-col table with double outer border: label (bold) | page number (large font)
    private function addNumberTable(Section $section, string $label, int $num, int $labelW, int $numW): void
    {
        $leftCell = [
            'cellMarginLeft'    => 120, 'cellMarginRight'   => 100,
            'cellMarginTop'     => 120, 'cellMarginBottom'  => 120,
            'borderTopStyle'    => 'double', 'borderTopSize'    => self::T_DBL, 'borderTopColor'    => 'auto',
            'borderBottomStyle' => 'double', 'borderBottomSize' => self::T_DBL, 'borderBottomColor' => 'auto',
            'borderLeftStyle'   => 'double', 'borderLeftSize'   => self::T_DBL, 'borderLeftColor'   => 'auto',
            'borderRightStyle'  => 'single', 'borderRightSize'  => self::T_THIN, 'borderRightColor'  => 'auto',
        ];
        $rightCell = [
            'cellMarginLeft'    => 120, 'cellMarginRight'   => 100,
            'cellMarginTop'     => 120, 'cellMarginBottom'  => 120,
            'borderTopStyle'    => 'double', 'borderTopSize'    => self::T_DBL, 'borderTopColor'    => 'auto',
            'borderBottomStyle' => 'double', 'borderBottomSize' => self::T_DBL, 'borderBottomColor' => 'auto',
            'borderLeftStyle'   => 'single', 'borderLeftSize'   => self::T_THIN, 'borderLeftColor'   => 'auto',
            'borderRightStyle'  => 'double', 'borderRightSize'  => self::T_DBL, 'borderRightColor'  => 'auto',
        ];

        $t = $section->addTable();
        $t->addRow();
        $t->addCell($labelW, $leftCell)->addText($label, self::FONT_B, ['spaceBefore' => 120, 'spaceAfter' => 120]);
        $t->addCell($numW,   $rightCell)->addText((string) $num, self::FONT_L, ['spaceAfter' => 160]);
    }

    private function fmt(mixed $date, string $format = 'd/m/Y'): string
    {
        if ($date === null) return '';
        if ($date instanceof \DateTimeInterface) return $date->format($format);
        return Carbon::parse((string) $date)->format($format);
    }
}
