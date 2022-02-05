package com.sipanduberadat.petugas.adapters

import android.content.Context
import android.view.LayoutInflater
import android.view.ViewGroup
import androidx.recyclerview.widget.RecyclerView
import com.sipanduberadat.petugas.R
import com.sipanduberadat.petugas.models.Sirine
import com.sipanduberadat.petugas.viewHolders.SirenViewHolder

class SirenAdapter(
    private val context: Context,
    private val items: List<Sirine>,
    private val onClick: (siren: Sirine) -> Unit
): RecyclerView.Adapter<SirenViewHolder>() {
    override fun onCreateViewHolder(parent: ViewGroup, viewType: Int): SirenViewHolder {
        val view = LayoutInflater.from(context).inflate(R.layout.layout_item_siren, parent,
            false)
        return SirenViewHolder(view)
    }

    override fun getItemCount(): Int {
        return items.size
    }

    override fun onBindViewHolder(holder: SirenViewHolder, position: Int) {
        holder.onBindItem(items[position], onClick)
    }
}