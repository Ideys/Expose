<?php

namespace Ideys\Content\Section\Provider;

/**
 * Map section provider.
 */
class MapProvider extends SectionProvider
{
    /**
     * Return all linkable sections to a Map section.
     * Exclude other Map sections and Dir sections.
     *
     * @return array
     */
    public function findLinkableSections()
    {
        return  $this->db
            ->fetchAll(
                'SELECT s.id, s.expose_section_id, '.
                's.type, s.slug, s.visibility, '.
                't.title, t.description, t.legend, t.parameters '.
                'FROM expose_section AS s '.
                'LEFT JOIN expose_section_trans AS t '.
                'ON t.expose_section_id = s.id '.
                'WHERE s.type NOT IN  (\'dir\', \'map\') '.
                'AND s.archive = 0 '.
                'ORDER BY s.hierarchy ');
    }
}
