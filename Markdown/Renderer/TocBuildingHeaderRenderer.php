<?php


namespace Dontdrinkandroot\GitkiBundle\Markdown\Renderer;

use League\CommonMark\Block\Element\AbstractBlock;
use League\CommonMark\Block\Element\Header;
use League\CommonMark\Block\Renderer\HeaderRenderer;
use League\CommonMark\HtmlRendererInterface;
use League\CommonMark\Inline\Element\AbstractInline;
use League\CommonMark\Inline\Element\AbstractInlineContainer;
use League\CommonMark\Inline\Element\Text;

class TocBuildingHeaderRenderer extends HeaderRenderer
{

    private $toc = [];

    private $title = null;

    private $count = 0;

    private $current = [];

    /**
     * {@inheritdoc}
     */
    public function render(AbstractBlock $block, HtmlRendererInterface $htmlRenderer, $inTightList = false)
    {
        if (!($block instanceof Header)) {
            throw new \InvalidArgumentException('Incompatible block type: ' . get_class($block));
        }

        $htmlElement = parent::render($block, $htmlRenderer, $inTightList);

        $id = 'heading' . $this->count;
        $level = $block->getLevel();
        $text = $this->getPlainText($block);

        $htmlElement->setAttribute('id', $id);
        if (null === $this->title && $level == 1) {
            $this->title = $text;
        } else {
            if ($level >= 2) {
                for ($i = $level; $i <= 6; $i++) {
                    unset($this->current[$i]);
                }
                $this->current[$level] = [
                    'id'       => $id,
                    'text'     => $text,
                    'level'    => $level,
                    'children' => []
                ];
                if ($level == 2) {
                    $this->toc[] = &$this->current[$level];
                } else {
                    if (isset($this->current[$level - 1])) {
                        $this->current[$level - 1]['children'][] = &$this->current[$level];
                    }
                }
            }
        }

        $this->count++;

        return $htmlElement;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @return array
     */
    public function getToc()
    {
        return $this->toc;
    }

    /**
     * @param Header $header
     *
     * @return string
     */
    private function getPlainText(Header $header)
    {
        $text = '';
        foreach ($header->getInlines() as $inline) {
            $text .= $this->getPlainInlineText($inline);
        }

        return $text;
    }

    /**
     * @param AbstractInline $inline
     *
     * @return string
     */
    private function getPlainInlineText(AbstractInline $inline)
    {
        if ($inline instanceof Text) {
            return $inline->getContent();
        }

        if ($inline instanceof AbstractInlineContainer) {
            $text = '';
            foreach ($inline->getChildren() as $child) {
                $text .= $this->getPlainInlineText($child);
            }

            return $text;
        }

        return '';
    }
}
