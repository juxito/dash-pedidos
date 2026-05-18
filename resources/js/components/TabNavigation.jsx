import React from 'react'
import { Link } from '@inertiajs/react'
import { cn } from '@/lib/utils'

export default function TabNavigation({ currentSection, sections }) {
    return (
        <div className="mb-4 border-b border-gray-200 dark:border-gray-700">
            <nav className="-mb-px flex space-x-8" aria-label="Tabs">
                {sections.map((section) => (
                    <Link
                        key={section.name}
                        href={route(`dashboard.${section.name}`)}
                        className={cn(
                            currentSection === section.name
                                ? 'border-indigo-500 text-indigo-600 dark:border-indigo-400 dark:text-indigo-400'
                                : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 dark:text-gray-400 dark:hover:border-gray-600 dark:hover:text-gray-300',
                            'whitespace-nowrap border-b-2 py-4 px-1 text-sm font-medium'
                        )}
                        aria-current={currentSection === section.name ? 'page' : undefined}
                    >
                        {section.label}
                    </Link>
                ))}
            </nav>
        </div>
    )
}
