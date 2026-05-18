import { Link } from '@inertiajs/react'

export default function Pagination({ links }) {
    function getClassName(active) {
        if (active) {
            return 'px-3 py-1 rounded text-sm bg-indigo-600 text-white'
        } else {
            return 'px-3 py-1 rounded text-sm bg-gray-200 text-gray-700 hover:bg-gray-300 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600'
        }
    }

    return (
        <div className="flex items-center justify-between mt-6">
            <nav className="flex flex-wrap items-center gap-2">
                {links.map((link) => (
                    <Link
                        key={link.label}
                        href={link.url || '#'}
                        preserveScroll
                        className={getClassName(link.active)}
                        dangerouslySetInnerHTML={{ __html: link.label }}
                    />
                ))}
            </nav>
        </div>
    )
}
