<?php

declare(strict_types=1);

namespace SolutionForest\InspireCms\VisualEditor\AI\Prompts;

class LayoutPromptBuilder
{
    /**
     * Get the system prompt for layout generation.
     */
    public function getSystemPrompt(): string
    {
        return <<<'PROMPT'
You are an expert web page layout designer. You create professional, modern, and responsive page layouts.

Your task is to generate JSON layouts that can be rendered by a visual page builder. You must follow the exact structure and only use the block types provided.

Guidelines:
1. Create visually appealing, well-balanced layouts
2. Use proper hierarchy (sections > containers > content blocks)
3. Ensure accessibility - include proper heading levels, alt text suggestions
4. Consider responsive design principles
5. Keep layouts practical and realistic
6. Use appropriate spacing and alignment
7. Generate unique IDs for each block (format: block_[random])

You MUST respond with valid JSON only. No explanations, no markdown formatting, just pure JSON.
PROMPT;
    }

    /**
     * Build a prompt for generating a complete layout.
     */
    public function buildLayoutPrompt(string $description, array $options = []): string
    {
        $template = $options['template'] ?? null;
        $style = $options['style'] ?? 'modern';
        $blocks = $options['blocks'] ?? [];

        $blocksInfo = $this->formatBlocksInfo($blocks);

        $prompt = <<<PROMPT
Create a page layout based on this description: "{$description}"

PROMPT;

        if ($template) {
            $prompt .= "Template type: {$template}\n";
        }

        $prompt .= <<<PROMPT
Design style: {$style}

Available block types:
{$blocksInfo}

Layout structure requirements:
- The root must be a "container" block
- Use "section" blocks to separate major page areas
- Use "grid" and "column" blocks for multi-column layouts
- Each block needs: id (string), type (string), props (object), children (array)

Example structure:
{
  "root": {
    "id": "block_root123",
    "type": "container",
    "props": {"maxWidth": "1200px"},
    "styles": {},
    "children": [
      {
        "id": "block_hero456",
        "type": "section",
        "props": {"paddingY": "80px"},
        "styles": {"backgroundColor": "#f8fafc"},
        "children": [
          {
            "id": "block_heading789",
            "type": "heading",
            "props": {"text": "Welcome", "level": 1, "alignment": "center"},
            "styles": {},
            "children": []
          }
        ]
      }
    ]
  }
}

Generate the complete layout JSON:
PROMPT;

        return $prompt;
    }

    /**
     * Build a prompt for suggesting next blocks.
     */
    public function buildSuggestionPrompt(array $context): string
    {
        $currentLayout = json_encode($context['layout'] ?? [], JSON_PRETTY_PRINT);
        $position = $context['position'] ?? 'end';

        return <<<PROMPT
Based on the current page layout, suggest 3-5 blocks that would complement it well.

Current layout:
{$currentLayout}

Position to add: {$position}

Respond with JSON in this format:
{
  "suggestions": [
    {
      "type": "block_type",
      "reason": "Why this block would work well here",
      "blockData": {
        "id": "block_xxx",
        "type": "block_type",
        "props": {...},
        "children": []
      }
    }
  ]
}
PROMPT;
    }

    /**
     * Build a prompt for generating block content.
     */
    public function buildContentPrompt(string $blockType, array $context): string
    {
        $pageContext = $context['pageContext'] ?? 'general website';
        $tone = $context['tone'] ?? 'professional';

        return <<<PROMPT
Generate appropriate content for a "{$blockType}" block.

Page context: {$pageContext}
Tone: {$tone}

Respond with JSON containing the props for this block type.
PROMPT;
    }

    /**
     * Format blocks info for the prompt.
     */
    protected function formatBlocksInfo(array $blocks): string
    {
        $lines = [];

        foreach ($blocks as $block) {
            $container = $block['isContainer'] ? ' (container)' : '';
            $lines[] = "- {$block['type']}: {$block['description']}{$container}";
        }

        return implode("\n", $lines);
    }

    /**
     * Build a prompt for editing existing content.
     */
    public function buildEditPrompt(string $instruction, array $currentBlock): string
    {
        $currentJson = json_encode($currentBlock, JSON_PRETTY_PRINT);

        return <<<PROMPT
Edit this block based on the instruction: "{$instruction}"

Current block:
{$currentJson}

Respond with the modified block JSON only.
PROMPT;
    }

    /**
     * Build a prompt for generating a section layout.
     */
    public function buildSectionPrompt(string $sectionType, array $options = []): string
    {
        $style = $options['style'] ?? 'modern';

        $sectionTemplates = [
            'hero' => 'A hero section with a large headline, subtext, and call-to-action button. Consider adding an image or background.',
            'features' => 'A features section with a grid of 3-4 feature cards, each with an icon, title, and description.',
            'testimonials' => 'A testimonials section showcasing customer reviews with quotes, names, and optional photos.',
            'pricing' => 'A pricing section with 2-3 pricing tiers showing features and pricing.',
            'team' => 'A team section with a grid of team member cards including photos, names, and roles.',
            'faq' => 'An FAQ section with common questions and answers.',
            'cta' => 'A call-to-action section with a compelling message and button.',
            'contact' => 'A contact section with contact information and/or a contact form layout.',
            'gallery' => 'An image gallery section with a grid of images.',
            'stats' => 'A statistics section showing key numbers/metrics.',
        ];

        $sectionDescription = $sectionTemplates[$sectionType] ?? "A {$sectionType} section.";

        return <<<PROMPT
Create a {$sectionType} section layout.

Description: {$sectionDescription}
Style: {$style}

Generate a section block with appropriate child blocks.
PROMPT;
    }
}
