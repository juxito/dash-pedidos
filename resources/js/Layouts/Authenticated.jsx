import React from 'react'
import { Link, usePage } from '@inertiajs/react'

export default function AuthenticatedLayout({ children }) {
    return (
        <div className="min-h-screen bg-gray-100">
            <nav className="bg-white shadow">
                <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div className="flex justify-between h-16">
                        <div className="flex items-center space-x-8">
                            <h1 className="text-xl font-bold text-gray-900">
                                Dashboard de Pedidos
                            </h1>
                        </div>
                        <div className="flex items-center space-x-4">
                            <span className="text-sm text-gray-600">Bienvenido</span>
                            <button
                                onClick={() => {
                                    const form = document.createElement('form')
                                    form.method = 'POST'
                                    form.action = '/logout'
                                    const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                                    const input = document.createElement('input')
                                    input.type = 'hidden'
                                    input.name = '_token'
                                    input.value = token
                                    form.appendChild(input)
                                    document.body.appendChild(form)
                                    form.submit()
                                }}
                                className="text-sm text-red-600 hover:text-red-800"
                            >
                                Cerrar Sesión
                            </button>
                        </div>
                    </div>
                </div>
            </nav>
            <div className="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                <div className="mb-6 border-b border-gray-200">
                    <nav className="flex space-x-8">
                        <Link href="/dashboard" className="pb-4 text-sm font-medium border-b-2 text-gray-500 hover:text-gray-700">
                            Por Enviar
                        </Link>
                        <Link href="/dashboard/retrasados" className="pb-4 text-sm font-medium border-b-2 text-gray-500 hover:text-gray-700">
                            Retrasados
                        </Link>
                        <Link href="/dashboard/entregados" className="pb-4 text-sm font-medium border-b-2 text-gray-500 hover:text-gray-700">
                            Entregados
                        </Link>
                        <Link href="/dashboard/cancelados" className="pb-4 text-sm font-medium border-b-2 text-gray-500 hover:text-gray-700">
                            Cancelados
                        </Link>
                    </nav>
                </div>
                <main>
                    {children}
                </main>
            </div>
        </div>
    )
}